<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CasePetitioner;
use App\Models\CaseRespondent;
use App\Models\CourtCase;
use App\Models\FileMovement;
use App\Models\Lawyer;
use App\Models\User;
use App\Services\RtftsCaseReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Picqer\Barcode\Types\TypeCode128;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;

class FilingController extends Controller
{
    public function __construct(private readonly RtftsCaseReference $rtftsReference)
    {
    }

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
            $registration = $this->rtftsReference->issue($caseYear);

            $latest = $case->latestMovement;

            $case->update([
                'case_type' => $request->case_type,
                'subject' => $request->subject,
                'description' => $request->description,
                'status' => 'filed',
                'final_case_number' => $registration['reference'],
                'final_case_year' => $caseYear,
                'registration_serial' => $registration['serial'],
                'permanent_barcode' => $registration['barcode'],
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
            $registration = $this->rtftsReference->issue($caseYear);

            $case->update([
                'final_case_number' => $registration['reference'],
                'final_case_year' => $caseYear,
                'registration_serial' => $registration['serial'],
                'permanent_barcode' => $registration['barcode'],
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
                'barcode_scanned' => $registration['barcode'],
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
            $normalizedBarcode = RtftsCaseReference::barcodeFromSearch($barcode);

            $case = CourtCase::query()
                ->where(function ($query) use ($barcode, $normalizedBarcode) {
                    $query->where('permanent_barcode', $barcode)
                        ->orWhere('final_case_number', $barcode);

                    if ($normalizedBarcode) {
                        $query->orWhere('permanent_barcode', $normalizedBarcode);
                    }
                })
                ->first();
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
        $generator = new BarcodeGeneratorSVG();
        $barcodeSvg = $generator->getBarcode($case->permanent_barcode, $generator::TYPE_CODE_128, 2, 70);

        $autoPrint = (bool) $request->boolean('auto', false);
        $next = $request->query('next');

        return view('admin.tracking.filing-print-label', compact('case', 'barcodeSvg', 'widthMm', 'heightMm', 'autoPrint', 'next'));
    }

    public function printLabelPdf(Request $request, CourtCase $case)
    {
        if (empty($case->permanent_barcode)) {
            return redirect()->route('admin.tracking.filing.index')->with('error', 'No permanent barcode found for this case.');
        }

        [$widthMm, $heightMm] = $this->resolvePrintSize($request);
        $pdf = $this->buildBarcodeLabelPdf($case, $widthMm, $heightMm, (string) $request->query('orientation', 'normal'));

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="BarcodeLabel_' . $case->permanent_barcode . '.pdf"')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function printLabelTspl(Request $request, CourtCase $case)
    {
        if (empty($case->permanent_barcode)) {
            return redirect()->route('admin.tracking.filing.index')->with('error', 'No permanent barcode found for this case.');
        }

        [$widthMm, $heightMm] = $this->resolvePrintSize($request);
        $tspl = $this->buildTsplLabel($case, $widthMm, $heightMm);

        return response($tspl)
            ->header('Content-Type', 'text/plain; charset=US-ASCII')
            ->header('Content-Disposition', 'attachment; filename="BarcodeLabel_' . $case->permanent_barcode . '.prn"');
    }

    public function printLabelDirect(Request $request, CourtCase $case)
    {
        if (empty($case->permanent_barcode)) {
            return redirect()->route('admin.tracking.filing.index')->with('error', 'No permanent barcode found for this case.');
        }

        [$widthMm, $heightMm] = $this->resolvePrintSize($request);
        $tspl = $this->buildTsplLabel($case, $widthMm, $heightMm);
        $result = $this->sendRawToGs2406t($tspl);

        if (!$result['ok']) {
            return back()->with('error', 'Direct print failed: ' . $result['message']);
        }

        return back()->with('success', 'Label sent directly to GS2406T printer.');
    }

    private function buildTsplLabel(CourtCase $case, float $widthMm, float $heightMm): string
    {
        $barcode = str_replace('"', '', (string) $case->permanent_barcode);
        $reference = str_replace('"', '', (string) $case->case_reference);
        $referenceX = max(8, (int) ((400 - (strlen($reference) * 16)) / 2));
        $barcodeTextX = max(8, (int) ((400 - (strlen($barcode) * 16)) / 2));
        $widthMm = $this->tsplNumber($widthMm);
        $heightMm = $this->tsplNumber($heightMm);

        return implode("\r\n", [
            "SIZE {$widthMm} mm,{$heightMm} mm",
            'GAP 2 mm,0 mm',
            'DIRECTION 1',
            'REFERENCE 0,0',
            'SPEED 4',
            'DENSITY 8',
            'SET TEAR ON',
            'CLS',
            'TEXT ' . $referenceX . ',5,"3",0,1,1,"' . $reference . '"',
            // A one-dot narrow bar keeps the 12-digit RTFTS Code 128 barcode
            // safely inside a 50 mm (400-dot at 203 dpi) label.
            'BARCODE 50,35,"128",80,0,0,2,4,"' . $barcode . '"',
            'TEXT ' . $barcodeTextX . ',132,"3",0,1,1,"' . $barcode . '"',
            'PRINT 1,1',
            '',
        ]);
    }

    private function sendRawToGs2406t(string $tspl): array
    {
        $dir = storage_path('app/labels');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $file = $dir . DIRECTORY_SEPARATOR . 'label_' . uniqid('', true) . '.prn';
        file_put_contents($file, $tspl);

        try {
            $errors = [];

            foreach ($this->printerShares() as $printerShare) {
                $batchCommand = base_path('scripts/print-raw.cmd')
                    . ' "' . str_replace('"', '""', $file) . '"'
                    . ' "' . str_replace('"', '""', $printerShare) . '"';
                $process = new Process([
                    $this->windowsCommandPath(),
                    '/D',
                    '/C',
                    $batchCommand,
                ]);
                $process->setTimeout(10);
                $process->run();

                $message = trim($process->getErrorOutput() ?: $process->getOutput());

                if ($process->isSuccessful()) {
                    Log::info('Barcode label sent to printer.', [
                        'printer_share' => $printerShare,
                        'label_file' => $file,
                        'message' => $message,
                    ]);

                    return ['ok' => true, 'message' => $message];
                }

                $errors[] = $printerShare . ': ' . ($message ?: 'Windows rejected the raw print job.');
            }

            Log::warning('Barcode direct print failed.', [
                'printer_errors' => $errors,
            ]);

            return [
                'ok' => false,
                'message' => implode(' | ', $errors),
            ];
        } finally {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    private function printerShares(): array
    {
        return array_values(array_unique(array_filter([
            config('services.barcode_printer.share'),
            config('services.barcode_printer.fallback_share'),
            '\\\\localhost\\GS2406T',
        ])));
    }

    private function windowsCommandPath(): string
    {
        $systemRoot = getenv('SystemRoot') ?: 'C:\\Windows';

        return $systemRoot . '\\System32\\cmd.exe';
    }

    private function tsplNumber(float $number): string
    {
        return rtrim(rtrim(number_format($number, 1, '.', ''), '0'), '.');
    }

    private function buildBarcodeLabelPdf(CourtCase $case, float $widthMm, float $heightMm, string $orientation = 'normal'): string
    {
        $pageWidth = $this->mmToPoint($widthMm);
        $pageHeight = $this->mmToPoint($heightMm);
        $rotated = in_array($orientation, ['clockwise', 'counter'], true);
        $labelWidth = $rotated ? $pageHeight : $pageWidth;
        $labelHeight = $rotated ? $pageWidth : $pageHeight;
        $barcodeText = (string) $case->permanent_barcode;
        $referenceText = (string) $case->case_reference;
        $barcode = (new TypeCode128())->getBarcode($barcodeText);

        $safeMarginX = $this->mmToPoint(4);
        $safeMarginY = $this->mmToPoint(8.5);
        $barcodeWidth = min($this->mmToPoint(40), $labelWidth - ($safeMarginX * 2));
        $barcodeHeight = min($this->mmToPoint(8), $labelHeight - ($safeMarginY * 2));
        $barcodeX = ($labelWidth - $barcodeWidth) / 2;
        $barcodeY = ($labelHeight - $barcodeHeight) / 2;
        $moduleWidth = $barcodeWidth / max(1, $barcode->getWidth());

        $content = [];
        $content[] = 'q';
        $content[] = '1 1 1 rg 0 0 ' . $this->pdfNumber($pageWidth) . ' ' . $this->pdfNumber($pageHeight) . ' re f';
        if ($orientation === 'clockwise') {
            $content[] = '0 1 -1 0 ' . $this->pdfNumber($pageWidth) . ' 0 cm';
        } elseif ($orientation === 'counter') {
            $content[] = '0 -1 1 0 0 ' . $this->pdfNumber($pageHeight) . ' cm';
        }
        $content[] = '0 0 0 rg';
        $content[] = $this->pdfText(
            $referenceText,
            9,
            $this->centerTextX($referenceText, 9, $labelWidth),
            $labelHeight - $this->mmToPoint(4)
        );

        $x = $barcodeX;
        foreach ($barcode->getBars() as $bar) {
            $barWidth = $bar->getWidth() * $moduleWidth;
            if ($bar->isBar()) {
                $content[] = $this->pdfNumber($x) . ' ' . $this->pdfNumber($barcodeY) . ' ' . $this->pdfNumber($barWidth) . ' ' . $this->pdfNumber($barcodeHeight) . ' re f';
            }
            $x += $barWidth;
        }

        $content[] = $this->pdfText(
            $barcodeText,
            8,
            $this->centerTextX($barcodeText, 8, $labelWidth),
            $this->mmToPoint(2.2)
        );

        $content[] = 'Q';

        return $this->buildPdfDocument($pageWidth, $pageHeight, implode("\n", $content) . "\n");
    }

    private function buildPdfDocument(float $pageWidth, float $pageHeight, string $content): string
    {
        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$this->pdfNumber($pageWidth)} {$this->pdfNumber($pageHeight)}] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
            "5 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n{$content}endstream\nendobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefAt = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefAt}\n%%EOF\n";

        return $pdf;
    }

    private function pdfText(string $text, float $fontSize, float $x, float $y): string
    {
        return 'BT /F1 ' . $this->pdfNumber($fontSize) . ' Tf ' . $this->pdfNumber($x) . ' ' . $this->pdfNumber($y) . ' Td (' . $this->pdfEscape($text) . ') Tj ET';
    }

    private function centerTextX(string $text, float $fontSize, float $pageWidth): float
    {
        $estimatedWidth = strlen($text) * $fontSize * 0.48;

        return max($this->mmToPoint(1), ($pageWidth - $estimatedWidth) / 2);
    }

    private function pdfEscape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function pdfNumber(float $number): string
    {
        return rtrim(rtrim(number_format($number, 3, '.', ''), '0'), '.');
    }

    private function mmToPoint(float $mm): float
    {
        return $mm * 72 / 25.4;
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
        $widthMm = (float) $request->input('width_mm', 50);
        $heightMm = (float) $request->input('height_mm', 25);

        $widthMm = max(20, min(110, $widthMm));
        $heightMm = max(20, min(150, $heightMm));

        return [$widthMm, $heightMm];
    }
}
