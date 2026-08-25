<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtCase;
use App\Models\CourtDispatchBatch;
use App\Models\CourtDispatchBatchItem;
use App\Models\FileMovement;
use App\Services\RtftsCaseReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectionReceiveController extends Controller
{
    public function show(Request $request)
    {
        $section = $this->resolveSection($request->user());
        $isAffidavit = stripos($section, 'affidavit') !== false;

        return view('admin.tracking.receive', compact('section', 'isAffidavit'));
    }

    public function receive(Request $request)
    {
        $request->validate([
            'barcode' => 'nullable|string|max:255',
            'barcodes' => 'nullable|string|max:20000',
            'action' => 'required|in:receive,reject',
            'reason' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $section = $this->resolveSection($user);

        if ($request->action === 'reject' && stripos($section, 'affidavit') === false) {
            return back()->with('error', 'Reject action is only allowed in affidavit section.');
        }

        if ($request->action === 'reject') {
            return $this->processSingleReject($request, $user, $section);
        }

        return $this->processBatchReceive($request, $user, $section);
    }

    public function validateIdentifier(Request $request)
    {
        $identifier = trim((string) $request->query('identifier', ''));
        $case = $this->findMovementCase($identifier);

        if ($case) {
            if ($message = $this->receiveRestrictionMessage($case, $request->user())) {
                return response()->json(['valid' => false, 'message' => $message], 422);
            }

            return response()->json([
                'valid' => true,
                'permanent_barcode' => $case->permanent_barcode,
                'case_number' => $case->final_case_number,
            ]);
        }

        $parsed = RtftsCaseReference::parseIdentifier($identifier);
        if ($parsed) {
            return response()->json([
                'valid' => true,
                'permanent_barcode' => $parsed['barcode'],
                'case_number' => $parsed['reference'],
            ]);
        }

        $message = CourtCase::where('temporary_barcode', $identifier)->exists()
            ? __('tracking.receive.temporary_not_accepted')
            : __('tracking.receive.identifier_not_found');

        return response()->json(['valid' => false, 'message' => $message], 422);
    }

    private function processSingleReject(Request $request, $user, string $section)
    {
        $barcode = trim((string) $request->input('barcode', ''));
        if ($barcode === '') {
            return back()->with('error', 'Barcode is required for reject action.');
        }

        $case = $this->findMovementCase($barcode);

        if (!$case) {
            $message = CourtCase::where('temporary_barcode', $barcode)->exists()
                ? __('tracking.receive.temporary_not_accepted')
                : __('tracking.receive.permanent_not_found');

            return back()->with('error', $message);
        }

        $latest = $case->latestMovement;
        $fromSection = $latest?->to_section ?? $case->current_section;

        DB::transaction(function () use ($request, $case, $user, $fromSection, $barcode) {
            FileMovement::create([
                'case_id' => $case->id,
                'barcode_scanned' => $case->permanent_barcode,
                'from_section' => $fromSection,
                'to_section' => $fromSection,
                'movement_type' => 'reject',
                'received_by_user_id' => $user->id,
                'received_at' => now(),
                'notes' => $request->reason,
            ]);
        });

        return back()->with('success', 'Affidavit rejection recorded. Custody remains with previous section.');
    }

    private function processBatchReceive(Request $request, $user, string $section)
    {
        $reason = $request->reason;
        $input = trim((string) $request->input('barcodes', ''));
        if ($input === '') {
            $input = trim((string) $request->input('barcode', ''));
        }

        $barcodes = $this->extractBarcodes($input);
        if (count($barcodes) === 0) {
            return back()->with('error', 'Please scan at least one barcode.');
        }

        $received = [];
        $failed = [];
        $courtReturnBatches = [];

        foreach ($barcodes as $barcode) {
            $case = $this->findMovementCase($barcode);

            if (!$case) {
                $parsed = RtftsCaseReference::parseIdentifier($barcode);

                if (!$parsed) {
                    $failureReason = CourtCase::where('temporary_barcode', $barcode)->exists()
                        ? __('tracking.receive.temporary_not_accepted')
                        : __('tracking.receive.permanent_not_found');
                    $failed[] = ['barcode' => $barcode, 'reason' => $failureReason];
                    continue;
                }

                $case = $this->createOldCaseForReceive($parsed, $user, $section, $reason);
                $received[] = [
                    'barcode' => $case->permanent_barcode,
                    'case_no' => $case->case_reference ?: ('CASE-' . $case->id),
                    'from_section' => 'Old Case Record',
                    'to_section' => $section,
                ];
                continue;
            }

            $latest = $case->latestMovement;
            $fromSection = $latest?->to_section ?? $case->current_section;

            if ($failureReason = $this->receiveRestrictionMessage($case, $user, $latest)) {
                $failed[] = ['barcode' => $barcode, 'reason' => $failureReason];
                continue;
            }

            if ((int) $case->current_holder_user_id === (int) $user->id) {
                $failed[] = [
                    'barcode' => $barcode,
                    'reason' => __('tracking.receive.already_in_your_custody'),
                ];
                continue;
            }

            if (strtolower((string) $fromSection) === 'court') {
                $error = $this->receiveFromCourt($case, $user, $section, $reason, $courtReturnBatches);
                if ($error) {
                    $failed[] = ['barcode' => $barcode, 'reason' => $error];
                    continue;
                }

                $received[] = [
                    'barcode' => $case->permanent_barcode,
                    'case_no' => $case->case_reference ?: ('CASE-' . $case->id),
                    'from_section' => 'Court',
                    'to_section' => $section,
                ];
                continue;
            }

            DB::transaction(function () use ($case, $user, $section, $fromSection, $reason) {
                $case->update([
                    'status' => 'in_progress',
                    'current_section' => $section,
                    'current_holder_user_id' => $user->id,
                    'current_holder_at' => now(),
                ]);

                FileMovement::create([
                    'case_id' => $case->id,
                    'barcode_scanned' => $case->permanent_barcode,
                    'from_section' => $fromSection,
                    'to_section' => $section,
                    'movement_type' => 'receive',
                    'received_by_user_id' => $user->id,
                    'received_at' => now(),
                    'notes' => $reason,
                ]);
            });

            $received[] = [
                'barcode' => $case->permanent_barcode,
                'case_no' => $case->case_reference ?: ('CASE-' . $case->id),
                'from_section' => $fromSection ?: '-',
                'to_section' => $section,
            ];
        }

        $message = count($received) . ' file(s) received successfully.';
        if (count($failed) > 0) {
            $message .= ' ' . count($failed) . ' failed.';
        }

        return back()->with('success', $message)->with('receive_summary', [
            'section' => $section,
            'received_by' => $user->name ?? 'Unknown',
            'received_at' => now()->format('Y-m-d h:i A'),
            'received' => $received,
            'failed' => $failed,
        ]);
    }

    private function extractBarcodes(string $input): array
    {
        $parts = preg_split('/[\r\n,\t]+/', $input) ?: [];
        $normalized = [];

        foreach ($parts as $part) {
            $barcode = trim($part);
            if ($barcode === '') {
                continue;
            }

            $normalized[$barcode] = $barcode;
        }

        return array_values($normalized);
    }

    private function findMovementCase(string $identifier): ?CourtCase
    {
        $identifier = trim(preg_replace('/\s+/', ' ', $identifier) ?? '');
        $normalizedBarcode = RtftsCaseReference::barcodeFromSearch($identifier);
        $normalizedReference = RtftsCaseReference::parseIdentifier($identifier)['reference'] ?? null;

        return CourtCase::query()
            ->where(function ($query) use ($identifier, $normalizedBarcode, $normalizedReference) {
                $query->where(function ($inner) use ($identifier) {
                    $inner->whereNotNull('permanent_barcode')
                        ->where('permanent_barcode', $identifier);
                })
                    ->orWhere(function ($inner) use ($identifier) {
                        $inner->whereNotNull('permanent_barcode')
                            ->where('final_case_number', $identifier);
                    });

                if ($normalizedBarcode) {
                    $query->orWhere(function ($inner) use ($normalizedBarcode) {
                        $inner->whereNotNull('permanent_barcode')
                            ->where('permanent_barcode', $normalizedBarcode);
                    });
                }

                if ($normalizedReference) {
                    $query->orWhere(function ($inner) use ($normalizedReference) {
                        $inner->whereNotNull('permanent_barcode')
                            ->where('final_case_number', $normalizedReference);
                    });
                }
            })
            ->first();
    }

    private function createOldCaseForReceive(array $parsed, $user, string $section, ?string $reason): CourtCase
    {
        return DB::transaction(function () use ($parsed, $user, $section, $reason) {
            $case = $this->findMovementCase($parsed['barcode']);
            if ($case) {
                return $case;
            }

            $now = now();
            $case = CourtCase::create([
                'initiated_by_user_id' => $user->id,
                'entry_source' => 'legacy',
                'status' => 'in_progress',
                'permanent_barcode' => $parsed['barcode'],
                'permanent_barcode_generated_at' => $now,
                'final_case_number' => $parsed['reference'],
                'final_case_year' => $parsed['year'],
                'registration_serial' => $parsed['serial'],
                'current_section' => $section,
                'current_holder_user_id' => $user->id,
                'current_holder_at' => $now,
            ]);

            FileMovement::create([
                'case_id' => $case->id,
                'barcode_scanned' => $parsed['input'],
                'from_section' => 'Old Case Record',
                'to_section' => $section,
                'movement_type' => 'legacy_intake',
                'received_by_user_id' => $user->id,
                'received_at' => $now,
                'notes' => $reason ?: 'Old case received by scan.',
            ]);

            $this->syncRegistrationSequence($parsed['year'], $parsed['serial']);

            return $case->fresh(['currentHolder']);
        });
    }

    private function syncRegistrationSequence(string $year, int $serial): void
    {
        DB::table('case_registration_sequences')->insertOrIgnore([
            'year' => $year,
            'last_serial' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $current = DB::table('case_registration_sequences')
            ->where('year', $year)
            ->lockForUpdate()
            ->value('last_serial');

        if ((int) $current < $serial) {
            DB::table('case_registration_sequences')
                ->where('year', $year)
                ->update([
                    'last_serial' => $serial,
                    'updated_at' => now(),
                ]);
        }
    }

    private function receiveFromCourt(CourtCase $case, $user, string $section, ?string $reason, array &$batches): ?string
    {
        $dispatch = $case->movements()
            ->where('movement_type', 'dispatch_to_court')
            ->whereNotNull('court_id')
            ->latest('received_at')
            ->first();

        if (!$dispatch) {
            return __('tracking.receive.court_dispatch_missing');
        }

        DB::transaction(function () use ($case, $user, $section, $reason, $dispatch, &$batches) {
            $courtId = (int) $dispatch->court_id;
            $batch = $batches[$courtId] ??= CourtDispatchBatch::create([
                'batch_no' => $this->nextBatchNo('RTN'),
                'court_id' => $courtId,
                'created_by_user_id' => $user->id,
                'type' => 'return',
                'returned_at' => now(),
                'handover_to_section' => $section,
                'notes' => $reason,
            ]);

            $case->update([
                'status' => 'in_progress',
                'current_section' => $section,
                'current_holder_user_id' => $user->id,
                'current_holder_at' => now(),
            ]);

            CourtDispatchBatchItem::create([
                'batch_id' => $batch->id,
                'case_id' => $case->id,
                'barcode_scanned' => $case->permanent_barcode,
                'from_section' => 'Court',
                'to_section' => $section,
                'processed_at' => now(),
            ]);

            FileMovement::create([
                'case_id' => $case->id,
                'court_id' => $courtId,
                'court_dispatch_batch_id' => $batch->id,
                'barcode_scanned' => $case->permanent_barcode,
                'from_section' => 'Court',
                'to_section' => $section,
                'movement_type' => 'returned_from_court_handover',
                'received_by_user_id' => $user->id,
                'received_at' => now(),
                'notes' => __('tracking.court.movement_notes.received_from_court'),
            ]);
        });

        return null;
    }

    private function receiveRestrictionMessage(CourtCase $case, $user, $latest = null): ?string
    {
        $latest ??= $case->latestMovement;
        $status = strtolower(trim((string) $case->status));

        if (in_array($status, ['rejected', 'affidavit_rejected', 'filing_rejected', 'returned_to_lawyer'], true)
            || $latest?->movement_type === 'reject') {
            return __('tracking.receive.rejected_file_not_receivable');
        }

        $fromSection = strtolower(trim((string) ($latest?->to_section ?? $case->current_section)));
        if ($fromSection === 'court' && !$this->canReceiveFromCourt($user)) {
            return __('tracking.receive.court_return_office_assistant_only');
        }

        return null;
    }

    private function canReceiveFromCourt($user): bool
    {
        return $user->hasRole('Super Admin')
            || str_contains(strtolower($this->resolveSection($user)), 'office assistant');
    }

    private function nextBatchNo(string $prefix): string
    {
        do {
            $number = $prefix . '-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (CourtDispatchBatch::where('batch_no', $number)->exists());

        return $number;
    }

    private function resolveSection($user): string
    {
        return $user->departmentRelation?->name
            ?? $user->department
            ?? 'Unassigned Section';
    }
}
