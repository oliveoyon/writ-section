<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CasePetitioner;
use App\Models\CaseRespondent;
use App\Models\CaseFile;
use App\Models\CourtCase;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LawyerCaseController extends Controller
{
    // Show create form
    public function create()
    {
        return view('website.lawyer.case_create', [
            'caseTypes' => CourtCase::caseTypes(),
        ]);
    }

    // Store case data
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'case_type' => ['required', 'string', 'max:255', Rule::in(CourtCase::caseTypes())],
            'description' => 'nullable|string',

            'petitioners.*.name_or_organization' => 'required|string|max:255',
            'petitioners.*.represented_by' => 'nullable|string|max:255',
            'petitioners.*.designation' => 'nullable|string|max:255',
            'petitioners.*.address' => 'nullable|string|max:1000',

            'respondents.*.name_or_organization' => 'required|string|max:255',
            'respondents.*.represented_by' => 'nullable|string|max:255',
            'respondents.*.designation' => 'nullable|string|max:255',
            'respondents.*.address' => 'nullable|string|max:1000',

            'files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // 1Create the case
        $case = CourtCase::create([
            'lawyer_id' => auth()->user()->lawyer->id,
            'case_type' => $request->case_type,
            'description' => $request->description,
            'temporary_barcode' => 'TEMP' . time(),
            'temporary_barcode_generated_at' => now(),
        ]);

        // Save Petitioners
        foreach ($request->petitioners as $p) {
            CasePetitioner::create([
                'case_id' => $case->id,
                'name_or_organization' => $p['name_or_organization'],
                'represented_by' => $p['represented_by'] ?? null,
                'designation' => $p['designation'] ?? null,
                'address' => $p['address'] ?? null,
            ]);
        }

        // Save Respondents
        foreach ($request->respondents as $r) {
            CaseRespondent::create([
                'case_id' => $case->id,
                'name_or_organization' => $r['name_or_organization'],
                'represented_by' => $r['represented_by'] ?? null,
                'designation' => $r['designation'] ?? null,
                'address' => $r['address'] ?? null,
            ]);
        }

        // Save uploaded files
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('case_files', 'public');

                CaseFile::create([
                    'case_id' => $case->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        // Redirect to case summary page
        return redirect()->route('lawyer.case.summary', $case->id);
    }

    // Case summary page after filing
    public function summary(CourtCase $case)
    {
        $this->ensureCaseOwner($case);
        return view('website.lawyer.case_summary', compact('case'));
    }

    // Show edit form
    public function edit(CourtCase $case)
    {
        $this->ensureCaseOwner($case);
        if (!in_array($case->status, ['draft', 'returned_to_lawyer'], true)) {
            abort(403, 'Only draft or returned cases can be edited.');
        }

        return view('website.lawyer.case_edit', [
            'case' => $case,
            'caseTypes' => CourtCase::caseTypes(),
        ]);
    }

    // Update case
    public function update(Request $request, CourtCase $case)
    {
        $this->ensureCaseOwner($case);
        if (!in_array($case->status, ['draft', 'returned_to_lawyer'], true)) {
            abort(403, 'Only draft or returned cases can be updated.');
        }

        $request->validate([
            'case_type' => ['required', 'string', 'max:255', Rule::in(CourtCase::caseTypes())],
            'description' => 'nullable|string',
            'petitioners.*.name_or_organization' => 'required|string|max:255',
            'petitioners.*.represented_by' => 'nullable|string|max:255',
            'petitioners.*.designation' => 'nullable|string|max:255',
            'petitioners.*.address' => 'nullable|string|max:1000',
            'respondents.*.name_or_organization' => 'required|string|max:255',
            'respondents.*.represented_by' => 'nullable|string|max:255',
            'respondents.*.designation' => 'nullable|string|max:255',
            'respondents.*.address' => 'nullable|string|max:1000',
            'files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Update case info
        $case->update([
            'case_type' => $request->case_type,
            'description' => $request->description,
        ]);

        // Update Petitioners (delete old and insert new)
        $case->petitioners()->delete();
        foreach ($request->petitioners as $p) {
            CasePetitioner::create([
                'case_id' => $case->id,
                'name_or_organization' => $p['name_or_organization'],
                'represented_by' => $p['represented_by'] ?? null,
                'designation' => $p['designation'] ?? null,
                'address' => $p['address'] ?? null,
            ]);
        }

        // Update Respondents (delete old and insert new)
        $case->respondents()->delete();
        foreach ($request->respondents as $r) {
            CaseRespondent::create([
                'case_id' => $case->id,
                'name_or_organization' => $r['name_or_organization'],
                'represented_by' => $r['represented_by'] ?? null,
                'designation' => $r['designation'] ?? null,
                'address' => $r['address'] ?? null,
            ]);
        }

        // Handle new file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('case_files', 'public');

                CaseFile::create([
                    'case_id' => $case->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('lawyer.case.summary', $case->id)
            ->with('success', 'Case updated successfully!');
    }

    public function resubmit(CourtCase $case)
    {
        $this->ensureCaseOwner($case);

        if ($case->status !== 'returned_to_lawyer') {
            return back()->with('error', 'Only returned cases can be resubmitted.');
        }

        $newTemp = $this->generateUniqueTempBarcode();
        $case->update([
            'status' => 'resubmitted',
            'temporary_barcode' => $newTemp,
            'temporary_barcode_generated_at' => now(),
        ]);

        return redirect()->route('lawyer.case.summary', $case->id)
            ->with('success', 'Case resubmitted successfully. New Temp ID: ' . $newTemp);
    }

    public function destroy(CourtCase $case)
    {
        $this->ensureCaseOwner($case);
        // Only allow deletion if case is draft
        if ($case->status != 'draft') {
            return redirect()->back()->with('error', 'Only draft cases can be deleted.');
        }

        // Delete associated petitioners
        $case->petitioners()->delete();

        // Delete associated respondents
        $case->respondents()->delete();

        // Delete associated files from storage and database
        foreach ($case->files as $file) {
            if (Storage::exists($file->file_path)) {
                Storage::delete($file->file_path);
            }
            $file->delete();
        }

        // Finally, delete the case itself
        $case->delete();

        return redirect()->back()->with('success', 'Draft case deleted successfully.');
    }


    // Generate Top-Sheet PDF
    public function printTopSheet(CourtCase $case)
    {
        $this->ensureCaseOwner($case);

        if (!$case->temporary_barcode) {
            return redirect()->back()->with('error', 'No temporary barcode available for this case.');
        }

        $case->loadMissing(['lawyer', 'petitioners', 'respondents']);

        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $barcode = base64_encode(
            $generator->getBarcode($case->temporary_barcode, $generator::TYPE_CODE_128, 2, 50)
        );

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,

            'fontDir' => array_merge($fontDirs, [
                public_path('assets/font'), // contains SolaimanLipi.ttf
            ]),

            'fontdata' => array_merge($fontData, [
                'solaimanlipi' => [
                    'R' => 'SolaimanLipi.ttf',

                    // ✅ THIS is what makes Bangla stable (from your working sample)
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
            ]),

            'default_font' => 'solaimanlipi',
            'autoScriptToLang' => true,
            'autoLangToFont'   => true,
        ]);

        $html = view('website.lawyer.top_sheet', compact('case', 'barcode'))->render();
        $mpdf->WriteHTML($html);

        return $mpdf->Output('TopSheet_' . $case->temporary_barcode . '.pdf', 'I');
    }

    private function ensureCaseOwner(CourtCase $case): void
    {
        $lawyerId = auth()->user()?->lawyer?->id;
        if (!$lawyerId || (int) $case->lawyer_id !== (int) $lawyerId) {
            abort(403, 'Unauthorized case access.');
        }
    }

    private function generateUniqueTempBarcode(): string
    {
        do {
            $candidate = 'TEMP' . now()->format('YmdHis') . random_int(100, 999);
        } while (CourtCase::where('temporary_barcode', $candidate)->exists());

        return $candidate;
    }

}
