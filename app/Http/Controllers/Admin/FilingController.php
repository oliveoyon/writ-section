<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CasePetitioner;
use App\Models\CaseRespondent;
use App\Models\CourtCase;
use App\Models\FileMovement;
use App\Models\Lawyer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mpdf\Mpdf;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Support\Str;

class FilingController extends Controller
{
    public function index()
    {
        $section = $this->resolveSection(request()->user());
        $recentCases = CourtCase::with(['lawyer', 'currentHolder'])
            ->where('current_section', $section)
            ->orderByDesc('current_holder_at')
            ->limit(20)
            ->get();

        $pendingTempCount = CourtCase::whereNotNull('temporary_barcode')
            ->whereNull('permanent_barcode')
            ->count();

        return view('admin.tracking.filing-home', compact('recentCases', 'pendingTempCount', 'section'));
    }

    public function showTempScan(Request $request)
    {
        $case = null;
        $isBlocked = false;
        $tempBarcode = trim((string) $request->query('temporary_barcode', ''));

        if ($tempBarcode !== '') {
            $case = CourtCase::with(['petitioners', 'respondents', 'lawyer'])
                ->where('temporary_barcode', $tempBarcode)
                ->first();

            if ($case && !empty($case->permanent_barcode)) {
                $isBlocked = true;
            }
        }

        return view('admin.tracking.filing-scan', compact('case', 'tempBarcode', 'isBlocked'));
    }

    public function receiveTemp(Request $request)
    {
        $request->validate([
            'temporary_barcode' => 'required|string|max:255',
            'case_type' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'petitioners' => 'required|array|min:1',
            'petitioners.*.name_or_organization' => 'required|string|max:255',
            'petitioners.*.represented_by' => 'nullable|string|max:255',
            'petitioners.*.phone' => 'nullable|string|max:20',
            'respondents' => 'required|array|min:1',
            'respondents.*.name' => 'required|string|max:255',
            'respondents.*.designation' => 'nullable|string|max:255',
            'respondents.*.organization' => 'nullable|string|max:255',
            'respondents.*.address' => 'nullable|string|max:255',
        ]);

        $case = CourtCase::where('temporary_barcode', $request->temporary_barcode)->first();

        if (!$case) {
            return back()->with('error', 'Temporary barcode not found.');
        }

        if (!empty($case->permanent_barcode)) {
            return back()->with('error', 'This file has already been converted to a permanent case.');
        }

        $user = $request->user();
        $section = $this->resolveSection($user);
        $petitioners = $this->normalizePetitioners($request->input('petitioners', []));
        $respondents = $this->normalizeRespondents($request->input('respondents', []));

        DB::transaction(function () use ($case, $request, $user, $section, $petitioners, $respondents) {
            $caseYear = (string) now()->year;
            $finalCaseNumber = 'WR-' . $caseYear . '-' . str_pad((string) $case->id, 6, '0', STR_PAD_LEFT);
            $permanentBarcode = 'WRIT-' . $caseYear . '-' . str_pad((string) $case->id, 8, '0', STR_PAD_LEFT);

            $latest = $case->latestMovement;

            $case->update([
                'case_type' => $request->case_type,
                'subject' => $request->subject,
                'description' => $request->description,
                'status' => 'filed',
                'final_case_number' => $finalCaseNumber,
                'final_case_year' => $caseYear,
                'permanent_barcode' => $permanentBarcode,
                'permanent_barcode_generated_at' => now(),
                'section_verified_at' => now(),
                'section_verified_by' => $user->id,
                'current_section' => $section,
                'current_holder_user_id' => $user->id,
                'current_holder_at' => now(),
            ]);
            $this->syncParties($case, $petitioners, $respondents);

            FileMovement::create([
                'case_id' => $case->id,
                'barcode_scanned' => $case->temporary_barcode,
                'from_section' => $latest?->to_section,
                'to_section' => $section,
                'movement_type' => 'receive',
                'received_by_user_id' => $user->id,
                'received_at' => now(),
                'notes' => 'Converted temporary filing to permanent case.',
            ]);
        });

        $freshCase = $case->fresh();
        return redirect()->route('admin.tracking.filing.print-label', [
            'case' => $freshCase->id,
            'auto' => 1,
            'next' => route('admin.tracking.filing.show', $freshCase),
        ])->with('success', 'File received and converted successfully. Permanent barcode: ' . $freshCase->permanent_barcode);
    }

    public function showDirectCreate()
    {
        return view('admin.tracking.filing-direct-create');
    }

    public function lookupLawyerMember(Request $request)
    {
        $request->validate([
            'member_id' => 'required|string|max:255',
        ]);

        $memberId = trim((string) $request->member_id);
        $existing = Lawyer::where('bar_council_id', $memberId)->first();
        if ($existing) {
            return response()->json([
                'found' => true,
                'existing' => true,
                'member' => [
                    'memberId' => $existing->bar_council_id,
                    'memberName' => $existing->full_name,
                    'mobile' => $existing->phone,
                    'email' => $existing->user?->email,
                ],
            ]);
        }

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.scba.org.bd/api/esl/memberlist");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new \Exception(curl_error($ch));
            }
            curl_close($ch);

            $data = json_decode($response, true);
            if (!$data || !is_array($data)) {
                throw new \Exception('API response invalid');
            }

            $member = array_filter($data, fn($m) => (string)($m['memberId'] ?? '') === $memberId);
            $member = array_values($member);

            if (empty($member)) {
                return response()->json([
                    'found' => false,
                    'message' => 'No member data found from API. Please enter manually.',
                ]);
            }

            return response()->json([
                'found' => true,
                'existing' => false,
                'member' => $member[0],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'found' => false,
                'message' => 'API lookup failed. Please enter manually.',
            ], 200);
        }
    }

    public function returnToLawyer(Request $request)
    {
        $request->validate([
            'temporary_barcode' => 'required|string|max:255',
            'return_reason' => 'required|string|max:1000',
        ]);

        $case = CourtCase::where('temporary_barcode', $request->temporary_barcode)->first();

        if (!$case) {
            return back()->with('error', 'Temporary barcode not found.');
        }

        if (!$case->lawyer_id) {
            return back()->with('error', 'This case has no lawyer owner. Return to lawyer is not available.');
        }

        if (!empty($case->permanent_barcode)) {
            return back()->with('error', 'Permanent file already generated. Cannot return this case to lawyer.');
        }

        $user = $request->user();
        $section = $this->resolveSection($user);
        $previousTemp = $case->temporary_barcode;

        DB::transaction(function () use ($case, $request, $user, $section, $previousTemp) {
            $case->update([
                'status' => 'returned_to_lawyer',
                'current_section' => 'Lawyer',
                'current_holder_user_id' => null,
                'current_holder_at' => null,
                'returned_at' => now(),
                'returned_by_user_id' => $user->id,
                'return_reason' => $request->return_reason,
                'temporary_barcode' => null,
                'temporary_barcode_generated_at' => null,
            ]);

            FileMovement::create([
                'case_id' => $case->id,
                'barcode_scanned' => $previousTemp,
                'from_section' => $section,
                'to_section' => 'Lawyer',
                'movement_type' => 'returned_to_lawyer',
                'received_by_user_id' => $user->id,
                'received_at' => now(),
                'notes' => $request->return_reason,
            ]);
        });

        return back()->with('success', 'Case returned to lawyer for correction.');
    }

    public function storeDirectCreate(Request $request)
    {
        $request->validate([
            'lawyer_member_id' => 'required|string|max:255',
            'lawyer_full_name' => 'required|string|max:255',
            'lawyer_phone' => 'nullable|string|max:20',
            'lawyer_email' => 'required|email|max:255',
            'lawyer_password' => 'nullable|string|min:6|max:255',
            'case_type' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'petitioners' => 'required|array|min:1',
            'petitioners.*.name_or_organization' => 'required|string|max:255',
            'petitioners.*.represented_by' => 'nullable|string|max:255',
            'petitioners.*.phone' => 'nullable|string|max:20',
            'respondents' => 'required|array|min:1',
            'respondents.*.name' => 'required|string|max:255',
            'respondents.*.designation' => 'nullable|string|max:255',
            'respondents.*.organization' => 'nullable|string|max:255',
            'respondents.*.address' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $section = $this->resolveSection($user);
        $petitioners = $this->normalizePetitioners($request->input('petitioners', []));
        $respondents = $this->normalizeRespondents($request->input('respondents', []));
        $lawyer = $this->findOrCreateLawyer($request);

        $case = DB::transaction(function () use ($request, $user, $section, $petitioners, $respondents, $lawyer) {
            $case = CourtCase::create([
                'lawyer_id' => $lawyer->id,
                'initiated_by_user_id' => $user->id,
                'entry_source' => 'filing',
                'case_type' => $request->case_type,
                'subject' => $request->subject,
                'description' => $request->description,
                'status' => 'filed',
            ]);

            $caseYear = (string) now()->year;
            $finalCaseNumber = 'WR-' . $caseYear . '-' . str_pad((string) $case->id, 6, '0', STR_PAD_LEFT);
            $permanentBarcode = 'WRIT-' . $caseYear . '-' . str_pad((string) $case->id, 8, '0', STR_PAD_LEFT);

            $case->update([
                'final_case_number' => $finalCaseNumber,
                'final_case_year' => $caseYear,
                'permanent_barcode' => $permanentBarcode,
                'permanent_barcode_generated_at' => now(),
                'section_verified_at' => now(),
                'section_verified_by' => $user->id,
                'current_section' => $section,
                'current_holder_user_id' => $user->id,
                'current_holder_at' => now(),
            ]);
            $this->syncParties($case, $petitioners, $respondents);

            FileMovement::create([
                'case_id' => $case->id,
                'barcode_scanned' => $permanentBarcode,
                'from_section' => null,
                'to_section' => $section,
                'movement_type' => 'receive',
                'received_by_user_id' => $user->id,
                'received_at' => now(),
                'notes' => 'Direct filing initiated at filing section.',
            ]);

            return $case;
        });

        return redirect()->route('admin.tracking.filing.print-label', [
            'case' => $case->id,
            'auto' => 1,
            'next' => route('admin.tracking.filing.show', $case),
        ])->with('success', 'Case created successfully. Permanent barcode: ' . $case->permanent_barcode);
    }

    public function show(CourtCase $case)
    {
        $case->load(['petitioners', 'respondents', 'lawyer', 'currentHolder', 'movements' => function ($q) {
            $q->latest('received_at')->limit(10);
        }]);

        return view('admin.tracking.filing-show', compact('case'));
    }

    public function printIndex(Request $request)
    {
        $barcode = trim((string) $request->query('permanent_barcode', ''));
        $case = null;

        if ($barcode !== '') {
            $case = CourtCase::where('permanent_barcode', $barcode)->first();
        }

        [$widthMm, $heightMm] = $this->resolvePrintSize($request);

        return view('admin.tracking.filing-print-search', compact('case', 'barcode', 'widthMm', 'heightMm'));
    }

    public function printLabel(Request $request, CourtCase $case)
    {
        if (empty($case->permanent_barcode)) {
            return redirect()->route('admin.tracking.filing.index')->with('error', 'No permanent barcode found for this case.');
        }

        [$widthMm, $heightMm] = $this->resolvePrintSize($request);
        $generator = new BarcodeGeneratorPNG();
        $barcodePng = base64_encode(
            $generator->getBarcode($case->permanent_barcode, $generator::TYPE_CODE_128, 2, 60)
        );

        $autoPrint = (bool) $request->boolean('auto', false);
        $next = $request->query('next');

        return view('admin.tracking.filing-print-label', compact('case', 'barcodePng', 'widthMm', 'heightMm', 'autoPrint', 'next'));
    }

    public function printLabelPdf(Request $request, CourtCase $case)
    {
        if (empty($case->permanent_barcode)) {
            return redirect()->route('admin.tracking.filing.index')->with('error', 'No permanent barcode found for this case.');
        }

        [$widthMm, $heightMm] = $this->resolvePrintSize($request);
        $generator = new BarcodeGeneratorPNG();
        $barcodePng = base64_encode(
            $generator->getBarcode($case->permanent_barcode, $generator::TYPE_CODE_128, 2, 60)
        );

        $html = view('admin.tracking.filing-print-label-pdf', compact('case', 'barcodePng', 'widthMm', 'heightMm'))->render();
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [$widthMm, $heightMm],
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 2,
            'margin_bottom' => 2,
        ]);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('BarcodeLabel_' . $case->permanent_barcode . '.pdf', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="BarcodeLabel_' . $case->permanent_barcode . '.pdf"');
    }

    private function findOrCreateLawyer(Request $request): Lawyer
    {
        $memberId = trim((string) $request->input('lawyer_member_id'));
        $fullName = trim((string) $request->input('lawyer_full_name'));
        $phone = trim((string) $request->input('lawyer_phone', ''));
        $email = trim((string) $request->input('lawyer_email'));
        $password = (string) $request->input('lawyer_password', '');

        $existingLawyer = Lawyer::where('bar_council_id', $memberId)->first();
        if ($existingLawyer) {
            return $existingLawyer;
        }

        $existingEmailUser = User::where('email', $email)->first();
        if ($existingEmailUser) {
            if ($existingEmailUser->lawyer) {
                return $existingEmailUser->lawyer;
            }
            $email = 'lawyer.' . strtolower($memberId) . '.' . time() . '@auto.local';
        }

        if ($password === '') {
            $password = Str::random(10);
        }

        $newUser = User::create([
            'name' => $fullName,
            'email' => $email,
            'password' => Hash::make($password),
            'user_type' => 'lawyer',
            'is_active' => 0,
        ]);

        return Lawyer::create([
            'user_id' => $newUser->id,
            'bar_council_id' => $memberId,
            'full_name' => $fullName,
            'phone' => $phone,
            'status' => 'active',
        ]);
    }

    private function resolveSection($user): string
    {
        return $user->departmentRelation?->name
            ?? $user->department
            ?? 'Filing Section';
    }

    private function syncParties(CourtCase $case, array $petitioners, array $respondents): void
    {
        $case->petitioners()->delete();
        foreach ($petitioners as $p) {
            CasePetitioner::create([
                'case_id' => $case->id,
                'name_or_organization' => $p['name_or_organization'],
                'represented_by' => $p['represented_by'] ?? null,
                'phone' => $p['phone'] ?? null,
            ]);
        }

        $case->respondents()->delete();
        foreach ($respondents as $r) {
            CaseRespondent::create([
                'case_id' => $case->id,
                'name' => $r['name'],
                'designation' => $r['designation'] ?? null,
                'organization' => $r['organization'] ?? null,
                'address' => $r['address'] ?? null,
            ]);
        }
    }

    private function normalizePetitioners(array $petitioners): array
    {
        return collect($petitioners)
            ->map(function ($p) {
                return [
                    'name_or_organization' => trim((string) ($p['name_or_organization'] ?? '')),
                    'represented_by' => trim((string) ($p['represented_by'] ?? '')),
                    'phone' => trim((string) ($p['phone'] ?? '')),
                ];
            })
            ->filter(fn ($p) => $p['name_or_organization'] !== '')
            ->values()
            ->all();
    }

    private function normalizeRespondents(array $respondents): array
    {
        return collect($respondents)
            ->map(function ($r) {
                return [
                    'name' => trim((string) ($r['name'] ?? '')),
                    'designation' => trim((string) ($r['designation'] ?? '')),
                    'organization' => trim((string) ($r['organization'] ?? '')),
                    'address' => trim((string) ($r['address'] ?? '')),
                ];
            })
            ->filter(fn ($r) => $r['name'] !== '')
            ->values()
            ->all();
    }

    private function resolvePrintSize(Request $request): array
    {
        $widthMm = (float) $request->query('width_mm', 60);
        $heightMm = (float) $request->query('height_mm', 40);

        $widthMm = max(30, min(110, $widthMm));
        $heightMm = max(20, min(150, $heightMm));

        return [$widthMm, $heightMm];
    }
}
