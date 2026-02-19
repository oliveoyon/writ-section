<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CasePetitioner;
use App\Models\CaseRespondent;
use App\Models\CaseFile;
use App\Models\CourtCase;
use Mpdf\Mpdf;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Support\Facades\Storage;

class LawyerCaseController extends Controller
{
    // Show create form
    public function create()
    {
        return view('website.lawyer.case_create');
    }

    // Store case data
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'case_type' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',

            'petitioners.*.name_or_organization' => 'required|string|max:255',
            'petitioners.*.represented_by' => 'nullable|string|max:255',
            'petitioners.*.phone' => 'nullable|string|max:20',

            'respondents.*.name' => 'required|string|max:255',
            'respondents.*.designation' => 'nullable|string|max:255',
            'respondents.*.organization' => 'nullable|string|max:255',
            'respondents.*.address' => 'nullable|string|max:255',

            'files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // 1Create the case
        $case = CourtCase::create([
            'lawyer_id' => auth()->user()->lawyer->id,
            'case_type' => $request->case_type,
            'subject' => $request->subject,
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
                'phone' => $p['phone'] ?? null,
            ]);
        }

        // Save Respondents
        foreach ($request->respondents as $r) {
            CaseRespondent::create([
                'case_id' => $case->id,
                'name' => $r['name'],
                'designation' => $r['designation'] ?? null,
                'organization' => $r['organization'] ?? null,
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
        return view('website.lawyer.case_summary', compact('case'));
    }

    // Show edit form
    public function edit(CourtCase $case)
    {
        if ($case->status !== 'draft') {
            abort(403, 'Only draft cases can be edited.');
        }

        return view('website.lawyer.case_edit', compact('case'));
    }

    // Update case
    public function update(Request $request, CourtCase $case)
    {
        if ($case->status !== 'draft') {
            abort(403, 'Only draft cases can be updated.');
        }

        $request->validate([
            'case_type' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'petitioners.*.name_or_organization' => 'required|string|max:255',
            'petitioners.*.represented_by' => 'nullable|string|max:255',
            'petitioners.*.phone' => 'nullable|string|max:20',
            'respondents.*.name' => 'required|string|max:255',
            'respondents.*.designation' => 'nullable|string|max:255',
            'respondents.*.organization' => 'nullable|string|max:255',
            'respondents.*.address' => 'nullable|string|max:255',
            'files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Update case info
        $case->update([
            'case_type' => $request->case_type,
            'subject' => $request->subject,
            'description' => $request->description,
        ]);

        // Update Petitioners (delete old and insert new)
        $case->petitioners()->delete();
        foreach ($request->petitioners as $p) {
            CasePetitioner::create([
                'case_id' => $case->id,
                'name_or_organization' => $p['name_or_organization'],
                'represented_by' => $p['represented_by'] ?? null,
                'phone' => $p['phone'] ?? null,
            ]);
        }

        // Update Respondents (delete old and insert new)
        $case->respondents()->delete();
        foreach ($request->respondents as $r) {
            CaseRespondent::create([
                'case_id' => $case->id,
                'name' => $r['name'],
                'designation' => $r['designation'] ?? null,
                'organization' => $r['organization'] ?? null,
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

    public function destroy(CourtCase $case)
    {
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
        $generator = new BarcodeGeneratorPNG();
        $barcode = base64_encode($generator->getBarcode($case->temporary_barcode, $generator::TYPE_CODE_128, 2, 50));

        $html = view('website.lawyer.top_sheet', compact('case', 'barcode'))->render();

        $mpdf = new Mpdf(['format' => 'A4']);
        $mpdf->WriteHTML($html);

        return $mpdf->Output('TopSheet_' . $case->temporary_barcode . '.pdf', 'I');
    }
}
