<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\CourtCase;
use App\Models\CourtDispatchBatch;
use App\Models\CourtDispatchBatchItem;
use App\Models\FileMovement;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class CourtDispatchController extends Controller
{
    public function dispatchIndex(Request $request)
    {
        $courts = Court::where('is_active', true)->orderBy('name_en')->get();

        return view('admin.tracking.court-dispatch', [
            'courts' => $courts,
        ]);
    }

    public function dispatchStore(Request $request)
    {
        $request->validate([
            'court_id' => 'required|exists:courts,id',
            'barcodes' => 'required|string|max:100000',
            'received_by_name' => 'nullable|string|max:255',
            'received_by_designation' => 'nullable|string|max:255',
            'received_by_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
        ]);

        $barcodes = $this->extractBarcodes((string) $request->barcodes);
        if (count($barcodes) === 0) {
            return back()->with('error', __('tracking.court.errors.no_barcodes'));
        }

        $court = Court::findOrFail((int) $request->court_id);
        $user = $request->user();

        $failed = [];
        $processed = [];

        $batch = DB::transaction(function () use ($barcodes, $court, $request, $user, &$failed, &$processed) {
            $batch = CourtDispatchBatch::create([
                'batch_no' => $this->nextBatchNo('DSP'),
                'court_id' => $court->id,
                'created_by_user_id' => $user?->id,
                'type' => 'dispatch',
                'dispatched_at' => now(),
                'received_by_name' => $request->received_by_name,
                'received_by_designation' => $request->received_by_designation,
                'received_by_phone' => $request->received_by_phone,
                'notes' => $request->notes,
            ]);

            foreach ($barcodes as $barcode) {
                $case = CourtCase::where('permanent_barcode', $barcode)
                    ->orWhere('temporary_barcode', $barcode)
                    ->first();

                if (!$case) {
                    $failed[] = ['barcode' => $barcode, 'reason' => __('tracking.court.errors.not_found')];
                    continue;
                }

                $latest = $case->latestMovement;
                $fromSection = $latest?->to_section ?? $case->current_section;

                if (strtolower((string) $fromSection) === 'court') {
                    $failed[] = ['barcode' => $barcode, 'reason' => __('tracking.court.errors.already_in_court')];
                    continue;
                }

                $case->update([
                    'current_section' => 'Court',
                    'current_holder_user_id' => null,
                    'current_holder_at' => now(),
                ]);

                $item = CourtDispatchBatchItem::create([
                    'batch_id' => $batch->id,
                    'case_id' => $case->id,
                    'barcode_scanned' => $barcode,
                    'from_section' => $fromSection,
                    'to_section' => 'Court',
                    'processed_at' => now(),
                ]);

                FileMovement::create([
                    'case_id' => $case->id,
                    'court_id' => $court->id,
                    'court_dispatch_batch_id' => $batch->id,
                    'barcode_scanned' => $barcode,
                    'from_section' => $fromSection,
                    'to_section' => 'Court',
                    'movement_type' => 'dispatch_to_court',
                    'received_by_user_id' => $user?->id,
                    'received_at' => now(),
                    'notes' => __('tracking.court.movement_notes.dispatch_to_court'),
                ]);

                $processed[] = [
                    'barcode' => $barcode,
                    'case_no' => $case->case_reference ?: ('CASE-' . $case->id),
                    'from_section' => $fromSection ?: '-',
                    'to_section' => 'Court',
                ];
            }

            return $batch;
        });

        if (count($processed) === 0) {
            return back()->with('error', __('tracking.court.errors.none_processed'))->with('court_failed', $failed);
        }

        return redirect()
            ->route('admin.tracking.court.dispatch.index')
            ->with('success', __('tracking.court.success.dispatch_done', ['count' => count($processed), 'batch' => $batch->batch_no]))
            ->with('court_processed', $processed)
            ->with('court_failed', $failed)
            ->with('court_batch_id', $batch->id);
    }

    public function returnIndex(Request $request)
    {
        $courts = Court::where('is_active', true)->orderBy('name_en')->get();
        $sections = Department::orderBy('name')->pluck('name');

        return view('admin.tracking.court-return', [
            'courts' => $courts,
            'sections' => $sections,
        ]);
    }

    public function returnStore(Request $request)
    {
        $request->validate([
            'court_id' => 'required|exists:courts,id',
            'handover_to_section' => 'required|string|max:255',
            'barcodes' => 'required|string|max:100000',
            'notes' => 'nullable|string|max:2000',
        ]);

        $barcodes = $this->extractBarcodes((string) $request->barcodes);
        if (count($barcodes) === 0) {
            return back()->with('error', __('tracking.court.errors.no_barcodes'));
        }

        $court = Court::findOrFail((int) $request->court_id);
        $user = $request->user();
        $handoverTo = trim((string) $request->handover_to_section);

        $failed = [];
        $processed = [];

        $batch = DB::transaction(function () use ($barcodes, $court, $request, $user, $handoverTo, &$failed, &$processed) {
            $batch = CourtDispatchBatch::create([
                'batch_no' => $this->nextBatchNo('RTN'),
                'court_id' => $court->id,
                'created_by_user_id' => $user?->id,
                'type' => 'return',
                'returned_at' => now(),
                'handover_to_section' => $handoverTo,
                'notes' => $request->notes,
            ]);

            foreach ($barcodes as $barcode) {
                $case = CourtCase::where('permanent_barcode', $barcode)
                    ->orWhere('temporary_barcode', $barcode)
                    ->first();

                if (!$case) {
                    $failed[] = ['barcode' => $barcode, 'reason' => __('tracking.court.errors.not_found')];
                    continue;
                }

                $latest = $case->latestMovement;
                $fromSection = $latest?->to_section ?? $case->current_section;

                if (strtolower((string) $fromSection) !== 'court') {
                    $failed[] = ['barcode' => $barcode, 'reason' => __('tracking.court.errors.not_in_court')];
                    continue;
                }

                CourtDispatchBatchItem::create([
                    'batch_id' => $batch->id,
                    'case_id' => $case->id,
                    'barcode_scanned' => $barcode,
                    'from_section' => 'Court',
                    'to_section' => $handoverTo,
                    'processed_at' => now(),
                ]);

                FileMovement::create([
                    'case_id' => $case->id,
                    'court_id' => $court->id,
                    'court_dispatch_batch_id' => $batch->id,
                    'barcode_scanned' => $barcode,
                    'from_section' => 'Court',
                    'to_section' => 'Court',
                    'movement_type' => 'returned_from_court_handover',
                    'received_by_user_id' => $user?->id,
                    'received_at' => now(),
                    'notes' => __('tracking.court.movement_notes.handover_to_section', ['section' => $handoverTo]),
                ]);

                $processed[] = [
                    'barcode' => $barcode,
                    'case_no' => $case->case_reference ?: ('CASE-' . $case->id),
                    'from_section' => 'Court',
                    'to_section' => $handoverTo,
                ];
            }

            return $batch;
        });

        if (count($processed) === 0) {
            return back()->with('error', __('tracking.court.errors.none_processed'))->with('court_failed', $failed);
        }

        return redirect()
            ->route('admin.tracking.court.return.index')
            ->with('success', __('tracking.court.success.return_handover_done', ['count' => count($processed), 'batch' => $batch->batch_no]))
            ->with('court_processed', $processed)
            ->with('court_failed', $failed)
            ->with('court_batch_id', $batch->id);
    }

    public function batchPdf(CourtDispatchBatch $batch)
    {
        $batch->load(['court', 'createdBy', 'items.courtCase']);

        $html = view('admin.tracking.court-batch-pdf', compact('batch'))->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'fontDir' => array_merge((new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'], [public_path('assets/font')]),
            'fontdata' => (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'] + [
                'solaimanlipi' => ['R' => 'SolaimanLipi.ttf'],
            ],
            'default_font' => 'solaimanlipi',
        ]);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('CourtBatch_' . $batch->batch_no . '.pdf', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="CourtBatch_' . $batch->batch_no . '.pdf"');
    }

    private function extractBarcodes(string $input): array
    {
        $parts = preg_split('/[\r\n,\t ]+/', $input) ?: [];
        $result = [];

        foreach ($parts as $part) {
            $code = trim($part);
            if ($code === '') {
                continue;
            }
            $result[$code] = $code;
        }

        return array_values($result);
    }

    private function nextBatchNo(string $prefix): string
    {
        do {
            $no = $prefix . '-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (CourtDispatchBatch::where('batch_no', $no)->exists());

        return $no;
    }
}
