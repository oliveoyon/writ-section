<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\CourtCase;
use App\Models\CourtDispatchBatch;
use App\Models\CourtDispatchBatchItem;
use App\Models\FileMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
                $case = $this->findMovementCase($barcode);

                if (!$case) {
                    $failed[] = ['barcode' => $barcode, 'reason' => $this->invalidBarcodeReason($barcode)];
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
                    'barcode_scanned' => $case->permanent_barcode,
                    'from_section' => $fromSection,
                    'to_section' => 'Court',
                    'processed_at' => now(),
                ]);

                FileMovement::create([
                    'case_id' => $case->id,
                    'court_id' => $court->id,
                    'court_dispatch_batch_id' => $batch->id,
                    'barcode_scanned' => $case->permanent_barcode,
                    'from_section' => $fromSection,
                    'to_section' => 'Court',
                    'movement_type' => 'dispatch_to_court',
                    'received_by_user_id' => $user?->id,
                    'received_at' => now(),
                    'notes' => __('tracking.court.movement_notes.dispatch_to_court'),
                ]);

                $processed[] = [
                    'barcode' => $case->permanent_barcode,
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

        return view('admin.tracking.court-return', [
            'courts' => $courts,
        ]);
    }

    public function returnStore(Request $request)
    {
        $request->validate([
            'court_id' => 'required|exists:courts,id',
            'barcodes' => 'required|string|max:100000',
            'notes' => 'nullable|string|max:2000',
        ]);

        $barcodes = $this->extractBarcodes((string) $request->barcodes);
        if (count($barcodes) === 0) {
            return back()->with('error', __('tracking.court.errors.no_barcodes'));
        }

        $court = Court::findOrFail((int) $request->court_id);
        $user = $request->user();
        $receivingSection = $user->departmentRelation?->name ?? $user->department;

        $failed = [];
        $processed = [];

        $batch = DB::transaction(function () use ($barcodes, $court, $request, $user, $receivingSection, &$failed, &$processed) {
            $batch = CourtDispatchBatch::create([
                'batch_no' => $this->nextBatchNo('RTN'),
                'court_id' => $court->id,
                'created_by_user_id' => $user?->id,
                'type' => 'return',
                'returned_at' => now(),
                'handover_to_section' => $receivingSection,
                'notes' => $request->notes,
            ]);

            foreach ($barcodes as $barcode) {
                $case = $this->findMovementCase($barcode);

                if (!$case) {
                    $failed[] = ['barcode' => $barcode, 'reason' => $this->invalidBarcodeReason($barcode)];
                    continue;
                }

                $latest = $case->latestMovement;
                $fromSection = $latest?->to_section ?? $case->current_section;

                if (strtolower((string) $fromSection) !== 'court') {
                    $failed[] = ['barcode' => $barcode, 'reason' => __('tracking.court.errors.not_in_court')];
                    continue;
                }

                $case->update([
                    'status' => 'in_progress',
                    'current_section' => $receivingSection,
                    'current_holder_user_id' => $user->id,
                    'current_holder_at' => now(),
                ]);

                CourtDispatchBatchItem::create([
                    'batch_id' => $batch->id,
                    'case_id' => $case->id,
                    'barcode_scanned' => $case->permanent_barcode,
                    'from_section' => 'Court',
                    'to_section' => $receivingSection,
                    'processed_at' => now(),
                ]);

                FileMovement::create([
                    'case_id' => $case->id,
                    'court_id' => $court->id,
                    'court_dispatch_batch_id' => $batch->id,
                    'barcode_scanned' => $case->permanent_barcode,
                    'from_section' => 'Court',
                    'to_section' => $receivingSection,
                    'movement_type' => 'returned_from_court_handover',
                    'received_by_user_id' => $user?->id,
                    'received_at' => now(),
                    'notes' => __('tracking.court.movement_notes.received_from_court'),
                ]);

                $processed[] = [
                    'barcode' => $case->permanent_barcode,
                    'case_no' => $case->case_reference ?: ('CASE-' . $case->id),
                    'from_section' => 'Court',
                    'to_section' => $receivingSection,
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
        $this->authorizeBatchView($batch, request()->user());
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

    public function batches(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
            'type' => 'nullable|in:dispatch,return',
            'court_id' => 'nullable|integer|exists:courts,id',
            'creator_id' => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date_format:d-m-Y',
            'date_to' => 'nullable|date_format:d-m-Y',
        ]);

        $user = $request->user();
        $canViewAll = $this->canViewAllBatches($user);
        $queryText = trim((string) $request->input('q', ''));
        $dateFrom = $request->filled('date_from')
            ? Carbon::createFromFormat('d-m-Y', (string) $request->date_from)->toDateString()
            : null;
        $dateTo = $request->filled('date_to')
            ? Carbon::createFromFormat('d-m-Y', (string) $request->date_to)->toDateString()
            : null;

        $query = CourtDispatchBatch::query()
            ->with(['court', 'createdBy', 'items'])
            ->when(!$canViewAll, fn ($builder) => $builder->where('created_by_user_id', $user->id))
            ->when($queryText !== '', function ($builder) use ($queryText) {
                $builder->where(function ($inner) use ($queryText) {
                    $inner->where('batch_no', 'like', '%' . $queryText . '%')
                        ->orWhereHas('items.courtCase', function ($caseQuery) use ($queryText) {
                            $caseQuery->where('final_case_number', 'like', '%' . $queryText . '%')
                                ->orWhere('permanent_barcode', 'like', '%' . $queryText . '%');
                        });
                });
            })
            ->when($request->filled('type'), fn ($builder) => $builder->where('type', $request->type))
            ->when($request->filled('court_id'), fn ($builder) => $builder->where('court_id', $request->court_id))
            ->when($canViewAll && $request->filled('creator_id'), fn ($builder) => $builder->where('created_by_user_id', $request->creator_id))
            ->when($dateFrom, fn ($builder) => $builder->whereDate(DB::raw('COALESCE(dispatched_at, returned_at)'), '>=', $dateFrom))
            ->when($dateTo, fn ($builder) => $builder->whereDate(DB::raw('COALESCE(dispatched_at, returned_at)'), '<=', $dateTo))
            ->latest(DB::raw('COALESCE(dispatched_at, returned_at)'));

        $batches = $query->paginate(20)->withQueryString();
        $this->attachBatchReturnCounts($batches->getCollection());

        $courts = Court::orderBy('name_en')->get();
        $creators = $canViewAll
            ? User::whereHas('courtDispatchBatches')->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('admin.tracking.court-batches', compact('batches', 'courts', 'creators', 'canViewAll'));
    }

    public function batchShow(CourtDispatchBatch $batch, Request $request)
    {
        $this->authorizeBatchView($batch, $request->user());
        $batch->load(['court', 'createdBy', 'items.courtCase']);

        $returnMovements = FileMovement::query()
            ->whereIn('case_id', $batch->items->pluck('case_id'))
            ->where('movement_type', 'returned_from_court_handover')
            ->orderBy('received_at')
            ->get()
            ->groupBy('case_id');

        $itemReturns = $batch->items->mapWithKeys(function (CourtDispatchBatchItem $item) use ($batch, $returnMovements) {
            $movement = null;
            if ($batch->type !== 'return') {
                $processedAt = $item->processed_at ?? $batch->dispatched_at ?? $item->created_at;
                $movement = ($returnMovements->get($item->case_id) ?? collect())
                    ->first(fn (FileMovement $candidate) => $candidate->received_at?->greaterThanOrEqualTo($processedAt));
            }

            return [$item->id => $movement];
        });

        return view('admin.tracking.court-batch-show', compact('batch', 'itemReturns'));
    }

    private function attachBatchReturnCounts($batches): void
    {
        $items = $batches->pluck('items')->flatten();
        $returnMovements = FileMovement::query()
            ->whereIn('case_id', $items->pluck('case_id')->unique())
            ->where('movement_type', 'returned_from_court_handover')
            ->orderBy('received_at')
            ->get()
            ->groupBy('case_id');

        foreach ($batches as $batch) {
            $returned = $batch->type === 'return'
                ? $batch->items->count()
                : $batch->items->filter(function (CourtDispatchBatchItem $item) use ($batch, $returnMovements) {
                    $processedAt = $item->processed_at ?? $batch->dispatched_at ?? $item->created_at;
                    return ($returnMovements->get($item->case_id) ?? collect())
                        ->contains(fn (FileMovement $movement) => $movement->received_at?->greaterThanOrEqualTo($processedAt));
                })->count();

            $batch->setAttribute('returned_items_count', $returned);
        }
    }

    private function authorizeBatchView(CourtDispatchBatch $batch, $user): void
    {
        abort_unless($this->canViewAllBatches($user) || (int) $batch->created_by_user_id === (int) $user?->id, 403);
    }

    private function canViewAllBatches($user): bool
    {
        return ($user?->user_type === 'admin') || $user?->hasRole('Super Admin');
    }

    private function extractBarcodes(string $input): array
    {
        $parts = preg_split('/[\r\n,\t]+/', $input) ?: [];
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

    private function findMovementCase(string $identifier): ?CourtCase
    {
        $identifier = trim(preg_replace('/\s+/', ' ', $identifier) ?? '');

        return CourtCase::query()
            ->whereNotNull('permanent_barcode')
            ->where('permanent_barcode', $identifier)
            ->orWhere(function ($query) use ($identifier) {
                $query->whereNotNull('permanent_barcode')
                    ->where('final_case_number', $identifier);
            })
            ->first();
    }

    private function nextBatchNo(string $prefix): string
    {
        do {
            $no = $prefix . '-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (CourtDispatchBatch::where('batch_no', $no)->exists());

        return $no;
    }

    private function invalidBarcodeReason(string $barcode): string
    {
        return CourtCase::where('temporary_barcode', $barcode)->exists()
            ? __('tracking.court.errors.temporary_barcode')
            : __('tracking.court.errors.not_found');
    }
}
