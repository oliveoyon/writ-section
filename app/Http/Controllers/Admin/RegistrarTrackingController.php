<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtCase;
use App\Models\FileMovement;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class RegistrarTrackingController extends Controller
{
    public function lookup(Request $request)
    {
        $case = null;

        if ($request->filled('q')) {
            $query = trim((string) $request->q);
            $case = CourtCase::with(['latestMovement.receivedBy', 'currentHolder'])
                ->where('temporary_barcode', $query)
                ->orWhere('permanent_barcode', $query)
                ->orWhere('final_case_number', $query)
                ->first();
        }

        return view('admin.tracking.lookup', compact('case'));
    }

    public function timeline(CourtCase $case)
    {
        $movements = $case->movements()->with('receivedBy')->orderBy('received_at', 'asc')->get();
        return view('admin.tracking.timeline', compact('case', 'movements'));
    }

    public function registerReport(Request $request)
    {
        $data = $this->prepareRegisterReportData($request);
        return view('admin.tracking.register-report', $data);
    }

    public function registerReportPdf(Request $request)
    {
        $data = $this->prepareRegisterReportData($request);
        $data['generatedAt'] = now();

        $html = view('admin.tracking.register-report-pdf', $data)->render();
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
        ]);
        $mpdf->WriteHTML($html);

        $filename = 'MovementRegister_' . ($data['dateFrom'] ?? now()->toDateString()) . '_to_' . ($data['dateTo'] ?? now()->toDateString()) . '.pdf';

        return response($mpdf->Output($filename, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    public function overrideReceive(Request $request, CourtCase $case)
    {
        $request->validate([
            'to_section' => 'required|string|max:255',
            'reason' => 'required|string|max:1000',
        ]);

        $user = $request->user();
        $latest = $case->latestMovement;
        $fromSection = $latest?->to_section ?? $case->current_section;

        DB::transaction(function () use ($request, $case, $user, $fromSection) {
            $case->update([
                'current_section' => $request->to_section,
                'current_holder_user_id' => $user->id,
                'current_holder_at' => now(),
            ]);

            FileMovement::create([
                'case_id' => $case->id,
                'barcode_scanned' => $case->permanent_barcode ?? $case->temporary_barcode,
                'from_section' => $fromSection,
                'to_section' => $request->to_section,
                'movement_type' => 'override_receive',
                'received_by_user_id' => $user->id,
                'received_at' => now(),
                'notes' => 'Registrar override performed.',
                'is_override' => true,
                'override_reason' => $request->reason,
            ]);
        });

        return redirect()->route('admin.tracking.timeline', $case)->with('success', 'Override recorded with audit reason.');
    }

    private function prepareRegisterReportData(Request $request): array
    {
        $user = $request->user();
        $userSection = trim((string) ($user?->departmentRelation?->name ?? $user?->department ?? ''));
        $userSectionLower = strtolower($userSection);
        $canViewAllSections = (($user?->user_type ?? '') === 'admin') || str_contains($userSectionLower, 'registrar');

        $today = now();
        $request->merge([
            'filter_mode' => (string) $request->input('filter_mode', 'date_range'),
            'date_from' => (string) $request->input('date_from', $today->toDateString()),
            'date_to' => (string) $request->input('date_to', $today->toDateString()),
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
            'movement_type' => 'nullable|in:receive,reject,override_receive,dispatch_to_court,returned_from_court_handover',
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
                'date_from' => 'required|date',
                'date_to' => 'required|date',
            ]);
            $dateFrom = Carbon::parse($validated['date_from'])->toDateString();
            $dateTo = Carbon::parse($validated['date_to'])->toDateString();

            if ($dateFrom > $dateTo) {
                [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
            }
        }

        $movementsQuery = FileMovement::with(['courtCase', 'receivedBy'])
            ->when($dateFrom !== '', fn ($q) => $q->whereDate('received_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($q) => $q->whereDate('received_at', '<=', $dateTo))
            ->when($movementType !== '', fn ($q) => $q->where('movement_type', $movementType));

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

        $movements = $movementsQuery->orderBy('received_at', 'asc')->get();

        if ($canViewAllSections) {
            $fromSections = FileMovement::query()
                ->select('from_section')
                ->whereNotNull('from_section')
                ->where('from_section', '<>', '')
                ->pluck('from_section');
            $toSections = FileMovement::query()
                ->select('to_section')
                ->whereNotNull('to_section')
                ->where('to_section', '<>', '')
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
