<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtCase;
use App\Models\Department;
use App\Models\FileMovement;
use App\Exceptions\FileTransferException;
use App\Services\CourtCaseSearch;
use App\Services\FileTransferService;
use App\Services\RtftsCaseReference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Mpdf\Mpdf;

class RegistrarTrackingController extends Controller
{
    private const REPORT_PAGE_SIZE = 100;

    private const PDF_MAX_ROWS = 5000;

    public function __construct(
        private readonly FileTransferService $transfers,
        private readonly CourtCaseSearch $caseSearch
    ) {}

    private const OVERRIDE_REASONS = [
        'incorrect_section' => 'Correct incorrect section assignment',
        'missed_scan' => 'Record a missed barcode scan',
        'misplaced_file' => 'Recover a misplaced file',
        'administrative_transfer' => 'Authorized administrative transfer',
    ];

    public function lookup(Request $request)
    {
        $case = null;
        $cases = collect();

        if ($request->filled('q')) {
            $query = trim((string) $request->q);
            $cases = $this->buildLookupQuery($query)
                ->with([
                    'latestMovement.receivedBy',
                    'latestMovement.court',
                    'latestMovement.transferItem.batch',
                    'currentHolder',
                    'petitioners',
                    'lawyer',
                    'activeTransferItem.batch',
                ])
                ->latest('id')
                ->limit(30)
                ->get();
            $case = $cases->first();
        }

        return view('admin.tracking.lookup', compact('case', 'cases'));
    }

    public function lookupSuggest(Request $request)
    {
        $query = trim((string) $request->input('q', ''));

        if (mb_strlen($query) < 3) {
            return response()->json(['items' => []]);
        }

        $cases = $this->buildLookupQuery($query)
            ->with(['petitioners', 'lawyer'])
            ->latest('id')
            ->limit(8)
            ->get();

        $items = $cases->map(function (CourtCase $case) {
            $title = $case->case_reference
                ?: $case->permanent_barcode
                ?: ('CASE-' . $case->id);

            $petitioner = $case->petitioners->first()?->name_or_organization;
            $lawyer = $case->lawyer?->full_name;
            $section = $case->current_section ?: 'N/A';

            $parts = array_values(array_filter([$petitioner, $lawyer, $section]));

            return [
                'id' => $case->id,
                'title' => $title,
                'subtitle' => implode(' | ', $parts),
                'url' => route('admin.tracking.lookup', ['q' => $title]),
            ];
        })->values();

        return response()->json(['items' => $items]);
    }

    private function buildLookupQuery(string $query): Builder
    {
        return $this->caseSearch->query($query);
    }

    public function timeline(CourtCase $case)
    {
        $case->load([
            'currentHolder:id,name',
            'activeTransferItem.batch',
            'cancelledTransferItems' => fn ($query) => $query
                ->with(['batch', 'cancelledBy:id,name'])
                ->latest('cancelled_at'),
        ]);
        $movements = $case->movements()
            ->with([
                'receivedBy:id,name',
                'transferItem.batch',
            ])
            ->orderBy('received_at', 'asc')
            ->get();
        $departments = Department::query()
            ->where('name', '<>', (string) $case->current_section)
            ->orderByRaw('COALESCE(display_name, name)')
            ->get();
        $overrideReasons = self::OVERRIDE_REASONS;

        return view('admin.tracking.timeline', compact('case', 'movements', 'departments', 'overrideReasons'));
    }

    public function registerReport(Request $request)
    {
        $data = $this->prepareRegisterReportData($request);
        return view('admin.tracking.register-report', $data);
    }

    public function registerReportPdf(Request $request)
    {
        $data = $this->prepareRegisterReportData($request, true);
        $data['generatedAt'] = now();

        $html = view('admin.tracking.register-report-pdf', $data)->render();

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,

            // Add font folder
            'fontDir' => array_merge($fontDirs, [
                public_path('assets/font'), // put SolaimanLipi.ttf here
            ]),

            // Register font with OTL settings (your proven working pattern)
            'fontdata' => array_merge($fontData, [
                'solaimanlipi' => [
                    'R' => 'SolaimanLipi.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
            ]),

            // Default font
            'default_font' => 'solaimanlipi',

            // Optional but helpful
            'autoScriptToLang' => true,
            'autoLangToFont'   => true,
        ]);

        $mpdf->WriteHTML($html);

        $filename = 'MovementRegister_' .
            ($data['dateFrom'] ?? now()->toDateString()) .
            '_to_' .
            ($data['dateTo'] ?? now()->toDateString()) .
            '.pdf';

        return response($mpdf->Output($filename, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    public function overrideReceive(Request $request, CourtCase $case)
    {
        if (!$case->permanent_barcode) {
            return back()->with('error', __('tracking.timeline.permanent_barcode_required'));
        }

        $request->validate([
            'to_department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')
                    ->where(fn ($query) => $query->where('name', '<>', (string) $case->current_section)),
            ],
            'reason' => ['required', Rule::in(array_keys(self::OVERRIDE_REASONS))],
        ]);

        $user = $request->user();
        $department = Department::findOrFail((int) $request->to_department_id);
        $reason = self::OVERRIDE_REASONS[$request->reason];
        try {
            DB::transaction(function () use ($case, $user, $department, $reason) {
                $case = CourtCase::query()->whereKey($case->id)->lockForUpdate()->firstOrFail();

                if ($this->transfers->pendingTransferForCase($case->id, true)) {
                    throw new FileTransferException('This file has a pending handover. Cancel that handover before using Registrar Override.');
                }

                $latest = $case->latestMovement;
                $fromSection = $latest?->to_section ?? $case->current_section;

                $case->update([
                    'current_section' => $department->name,
                    'current_holder_user_id' => null,
                    'current_holder_at' => now(),
                ]);

                FileMovement::create([
                    'case_id' => $case->id,
                    'barcode_scanned' => $case->permanent_barcode,
                    'from_section' => $fromSection,
                    'to_section' => $department->name,
                    'movement_type' => 'override_receive',
                    'received_by_user_id' => $user->id,
                    'received_at' => now(),
                    'notes' => 'Registrar override performed.',
                    'is_override' => true,
                    'override_reason' => $reason,
                ]);
            }, 3);
        } catch (FileTransferException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.tracking.timeline', $case)->with('success', 'Override recorded with audit reason.');
    }

    private function prepareRegisterReportData(Request $request, bool $forPdf = false): array
    {
        $user = $request->user();
        $userSection = trim((string) ($user?->departmentRelation?->name ?? $user?->department ?? ''));
        $userSectionLower = strtolower($userSection);
        $canViewAllSections = (($user?->user_type ?? '') === 'admin') || str_contains($userSectionLower, 'registrar');

        $today = now();
        $request->merge([
            'filter_mode' => (string) $request->input('filter_mode', 'date_range'),
            'date_from' => (string) $request->input('date_from', $today->format('d-m-Y')),
            'date_to' => (string) $request->input('date_to', $today->format('d-m-Y')),
            'month' => (string) $request->input('month', $today->format('Y-m')),
            'year' => (int) $request->input('year', (int) $today->format('Y')),
            'movement_scope' => (string) $request->input('movement_scope', 'all'),
        ]);

        $filterMode = (string) $request->input('filter_mode');
        $dateFrom = (string) $request->input('date_from');
        $dateTo = (string) $request->input('date_to');
        $month = (string) $request->input('month');
        $year = (int) $request->input('year');
        $section = trim((string) $request->input('section', ''));
        $movementType = trim((string) $request->input('movement_type', ''));
        $movementScope = trim((string) $request->input('movement_scope', 'all'));

        if (!$canViewAllSections) {
            $section = $userSection;
        }

        $request->validate([
            'filter_mode' => 'required|in:date_range,month,year',
            'section' => 'nullable|string|max:255',
            'movement_type' => 'nullable|in:receive,reject,override_receive,dispatch_to_court,returned_from_court_handover,user_handover,old_case_receive,legacy_intake,legacy_receive',
            'movement_scope' => 'required|in:all,in,out',
        ]);

        if ($filterMode === 'month') {
            $request->validate(['month' => 'required|date_format:Y-m']);
            $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
            $dateFrom = $monthStart->toDateString();
            $dateTo = $monthEnd->toDateString();
        } elseif ($filterMode === 'year') {
            $request->validate(['year' => 'required|integer|min:2000|max:2100']);
            $yearStart = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $yearEnd = Carbon::createFromDate($year, 12, 31)->endOfDay();
            $dateFrom = $yearStart->toDateString();
            $dateTo = $yearEnd->toDateString();
        } else {
            $validated = $request->validate([
                'date_from' => 'required|date_format:d-m-Y',
                'date_to' => 'required|date_format:d-m-Y',
            ]);
            $dateFrom = Carbon::createFromFormat('d-m-Y', $validated['date_from'])->toDateString();
            $dateTo = Carbon::createFromFormat('d-m-Y', $validated['date_to'])->toDateString();

            if ($dateFrom > $dateTo) {
                [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
            }
        }

        $rangeStart = $dateFrom !== '' ? Carbon::parse($dateFrom)->startOfDay() : null;
        $rangeEnd = $dateTo !== '' ? Carbon::parse($dateTo)->endOfDay() : null;

        $movementsQuery = FileMovement::with([
                'courtCase',
                'receivedBy',
                'transferItem.batch',
            ])
            ->when($rangeStart, fn($q) => $q->where('received_at', '>=', $rangeStart))
            ->when($rangeEnd, fn($q) => $q->where('received_at', '<=', $rangeEnd))
            ->when($movementType !== '', function ($q) use ($movementType) {
                if ($movementType === 'old_case_receive') {
                    $q->whereIn('movement_type', ['legacy_intake', 'legacy_receive']);

                    return;
                }

                $q->where('movement_type', $movementType);
            });

        if (!$canViewAllSections && $userSection !== '') {
            if ($movementScope === 'in') {
                $movementsQuery->where('to_section', $userSection);
            } elseif ($movementScope === 'out') {
                $movementsQuery->where('from_section', $userSection);
            } else {
                $movementsQuery->where(function ($inner) use ($userSection) {
                    $inner->where('to_section', $userSection)->orWhere('from_section', $userSection);
                });
            }
        } else {
            if ($section !== '') {
                if ($movementScope === 'in') {
                    $movementsQuery->where('to_section', $section);
                } elseif ($movementScope === 'out') {
                    $movementsQuery->where('from_section', $section);
                } else {
                    $movementsQuery->where(function ($inner) use ($section) {
                        $inner->where('to_section', $section)->orWhere('from_section', $section);
                    });
                }
            } else {
                if ($movementScope === 'in') {
                    $movementsQuery->whereNotNull('to_section')->where('to_section', '<>', '');
                } elseif ($movementScope === 'out') {
                    $movementsQuery->whereNotNull('from_section')->where('from_section', '<>', '');
                }
            }
        }

        if ($forPdf) {
            $matchingRows = (clone $movementsQuery)->toBase()->getCountForPagination();
            if ($matchingRows > self::PDF_MAX_ROWS) {
                throw ValidationException::withMessages([
                    'date_from' => 'This PDF contains '.number_format($matchingRows).' movements. Narrow the filters to '.number_format(self::PDF_MAX_ROWS).' or fewer records.',
                ]);
            }

            $movements = $movementsQuery
                ->orderBy('received_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();
        } else {
            $movements = $movementsQuery
                ->orderBy('received_at', 'asc')
                ->orderBy('id', 'asc')
                ->paginate(self::REPORT_PAGE_SIZE)
                ->withQueryString();
        }

        if ($canViewAllSections) {
            $fromSections = FileMovement::query()
                ->select('from_section')
                ->whereNotNull('from_section')
                ->where('from_section', '<>', '')
                ->distinct()
                ->pluck('from_section');
            $toSections = FileMovement::query()
                ->select('to_section')
                ->whereNotNull('to_section')
                ->where('to_section', '<>', '')
                ->distinct()
                ->pluck('to_section');
            $sections = $fromSections->merge($toSections)->unique()->sort()->values();
        } else {
            $sections = collect($userSection !== '' ? [$userSection] : []);
        }

        return [
            'movements' => $movements,
            'sections' => $sections,
            'canViewAllSections' => $canViewAllSections,
            'userSection' => $userSection,
            'filterMode' => $filterMode,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'month' => $month,
            'year' => $year,
            'section' => $section,
            'movementType' => $movementType,
            'movementScope' => $movementScope,
        ];
    }

}
