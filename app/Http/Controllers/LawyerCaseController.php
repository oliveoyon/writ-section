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
        $request->validate([
            'case_type' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'petitioners.*.name' => 'required|string|max:255',
            'respondents.*.name' => 'required|string|max:255',
            'files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB limit
        ]);

        // 1️⃣ Create the case
        $case = CourtCase::create([
            'lawyer_id' => auth()->user()->lawyer->id,
            'case_type' => $request->case_type,
            'subject' => $request->subject,
            'description' => $request->description,
            'temporary_barcode' => 'TEMP' . time(),
            'temporary_barcode_generated_at' => now(),
        ]);

        // 2️⃣ Save Petitioners
        foreach ($request->petitioners as $p) {
            CasePetitioner::create([
                'case_id' => $case->id,
                'name' => $p['name'],
                'address' => $p['address'] ?? null,
                'phone' => $p['phone'] ?? null,
                'email' => $p['email'] ?? null,
                'nid' => $p['nid'] ?? null,
            ]);
        }

        // 3️⃣ Save Respondents
        foreach ($request->respondents as $r) {
            CaseRespondent::create([
                'case_id' => $case->id,
                'name' => $r['name'],
                'designation' => $r['designation'] ?? null,
                'organization' => $r['organization'] ?? null,
                'address' => $r['address'] ?? null,
            ]);
        }

        // 4️⃣ Save uploaded files
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('case_files');

                CaseFile::create([
                    'case_id' => $case->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        // 5️⃣ Redirect to Top Sheet PDF
        return redirect()->route('lawyer.case.top_sheet', $case->id);
    }

    // Generate Top-Sheet PDF
    public function printTopSheet(CourtCase $case)
    {
        $generator = new BarcodeGeneratorPNG();
        $barcode = base64_encode($generator->getBarcode($case->temporary_barcode, $generator::TYPE_CODE_128, 2, 50));

        $html = view('website.lawyer.top_sheet', compact('case', 'barcode'))->render();

        $mpdf = new Mpdf(['format' => 'A4']);
        $mpdf->WriteHTML($html);

        return $mpdf->Output('TopSheet_'.$case->temporary_barcode.'.pdf', 'I');
    }
}
