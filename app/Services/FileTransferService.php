<?php

namespace App\Services;

use App\Exceptions\FileTransferException;
use App\Models\CourtCase;
use App\Models\FileMovement;
use App\Models\FileTransferBatch;
use App\Models\FileTransferItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FileTransferService
{
    public const MAX_FILES_PER_BATCH = 200;

    private const BLOCKED_CASE_STATUSES = [
        'rejected',
        'affidavit_rejected',
        'filing_rejected',
        'returned_to_lawyer',
    ];

    public function eligibleRecipientQuery(User $sender): Builder
    {
        return User::query()
            ->with('departmentRelation:id,name,display_name')
            ->whereKeyNot($sender->id)
            ->where('is_active', true)
            ->whereIn('user_type', ['admin', 'staff'])
            ->whereNotNull('department');
    }

    public function sendRestrictionMessage(CourtCase $case, User $sender): ?string
    {
        $activeCaseIds = FileTransferItem::query()
            ->pending()
            ->where('active_case_id', $case->id)
            ->pluck('active_case_id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        $latestMovementTypes = collect([
            $case->id => $case->latestMovement?->movement_type,
        ]);

        return $this->sendRestriction($case, $sender, $activeCaseIds, $latestMovementTypes);
    }

    public function pendingTransferForCase(int $caseId, bool $lockForUpdate = false): ?FileTransferItem
    {
        return FileTransferItem::query()
            ->with('batch:id,batch_no,sender_name,recipient_name,recipient_user_id')
            ->pending()
            ->where('active_case_id', $caseId)
            ->when($lockForUpdate, fn (Builder $query) => $query->lockForUpdate())
            ->first();
    }

    /**
     * @return array{batch: FileTransferBatch|null, sent: Collection, failed: array<int, array{case_id:int, case_no:string, reason:string}>}
     */
    public function send(User $sender, User $recipient, array $caseIds, ?string $notes = null): array
    {
        $caseIds = collect($caseIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($caseIds->isEmpty()) {
            throw new FileTransferException('Please scan at least one file.');
        }

        if ($caseIds->count() > self::MAX_FILES_PER_BATCH) {
            throw new FileTransferException('A transfer batch can contain at most '.self::MAX_FILES_PER_BATCH.' files.');
        }

        return DB::transaction(function () use ($sender, $recipient, $caseIds, $notes) {
            $lockedUsers = User::query()
                ->with('departmentRelation')
                ->whereIn('id', [$sender->id, $recipient->id])
                ->orderBy('id')
                ->get()
                ->keyBy('id');

            $lockedSender = $lockedUsers->get($sender->id);
            $lockedRecipient = $lockedUsers->get($recipient->id);
            $this->assertEligibleParticipants($lockedSender, $lockedRecipient);

            $cases = CourtCase::query()
                ->whereIn('id', $caseIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $activeCaseIds = FileTransferItem::query()
                ->pending()
                ->whereIn('active_case_id', $caseIds)
                ->lockForUpdate()
                ->pluck('active_case_id')
                ->map(fn ($id) => (int) $id)
                ->flip();

            $latestMovementTypes = $this->latestMovementTypes($cases->keys());
            $validCases = collect();
            $failed = [];

            foreach ($caseIds as $caseId) {
                $case = $cases->get($caseId);
                $reason = $this->sendRestriction($case, $lockedSender, $activeCaseIds, $latestMovementTypes);

                if ($reason !== null) {
                    $failed[] = [
                        'case_id' => $caseId,
                        'case_no' => $case?->case_reference ?? ('Case #'.$caseId),
                        'reason' => $reason,
                    ];
                    continue;
                }

                $validCases->push($case);
            }

            if ($validCases->isEmpty()) {
                return ['batch' => null, 'sent' => collect(), 'failed' => $failed];
            }

            $sentAt = now();
            $batch = FileTransferBatch::create([
                'batch_no' => null,
                'sender_user_id' => $lockedSender->id,
                'recipient_user_id' => $lockedRecipient->id,
                'sender_department_id' => $lockedSender->departmentRelation?->id,
                'recipient_department_id' => $lockedRecipient->departmentRelation?->id,
                'sender_name' => $lockedSender->name,
                'sender_employee_id' => $lockedSender->employee_id,
                'sender_section' => $lockedSender->departmentRelation?->name,
                'recipient_name' => $lockedRecipient->name,
                'recipient_employee_id' => $lockedRecipient->employee_id,
                'recipient_section' => $lockedRecipient->departmentRelation?->name,
                'status' => FileTransferBatch::STATUS_PENDING,
                'sent_at' => $sentAt,
                'notes' => $this->nullableText($notes),
            ]);

            $batch->update([
                'batch_no' => 'TRF-'.$sentAt->format('Ymd').'-'.str_pad((string) $batch->id, 8, '0', STR_PAD_LEFT),
            ]);

            $timestamps = ['created_at' => $sentAt, 'updated_at' => $sentAt];
            FileTransferItem::insert($validCases->map(fn (CourtCase $case) => array_merge([
                'batch_id' => $batch->id,
                'case_id' => $case->id,
                'active_case_id' => $case->id,
                'status' => FileTransferItem::STATUS_PENDING,
                'sent_at' => $sentAt,
            ], $timestamps))->all());

            return [
                'batch' => $batch->fresh(['sender', 'recipient']),
                'sent' => $validCases,
                'failed' => $failed,
            ];
        }, 3);
    }

    public function receive(User $recipient, int $caseId, ?string $notes = null): FileTransferItem
    {
        return DB::transaction(function () use ($recipient, $caseId, $notes) {
            $case = CourtCase::query()->whereKey($caseId)->lockForUpdate()->first();
            if (!$case) {
                throw new FileTransferException('File not found.');
            }

            $item = FileTransferItem::query()
                ->pending()
                ->where('active_case_id', $case->id)
                ->lockForUpdate()
                ->first();
            if (!$item) {
                throw new FileTransferException('This file has not been sent to you.');
            }

            $batch = FileTransferBatch::query()->whereKey($item->batch_id)->lockForUpdate()->firstOrFail();
            $lockedRecipient = User::query()
                ->with('departmentRelation')
                ->whereKey($recipient->id)
                ->first();

            if (!$lockedRecipient || !$lockedRecipient->is_active || !$lockedRecipient->departmentRelation) {
                throw new FileTransferException('Your account must be active and assigned to a department.');
            }

            if ((int) $batch->recipient_user_id !== (int) $lockedRecipient->id) {
                throw new FileTransferException('This file was sent to another user.');
            }

            if ((int) $case->current_holder_user_id !== (int) $batch->sender_user_id) {
                throw new FileTransferException('File custody changed after it was sent. Ask an administrator to review it.');
            }

            if ($this->caseIsBlocked($case) || $case->latestMovement?->movement_type === 'reject') {
                throw new FileTransferException('This rejected file cannot be received.');
            }

            if (strcasecmp((string) $case->current_section, 'Court') === 0) {
                throw new FileTransferException('A file in court must use the court return workflow.');
            }

            $receivedAt = now();
            $fromSection = $case->current_section ?: $batch->sender_section;
            $toSection = $lockedRecipient->departmentRelation->name;

            $case->update([
                'status' => 'in_progress',
                'current_section' => $toSection,
                'current_holder_user_id' => $lockedRecipient->id,
                'current_holder_at' => $receivedAt,
            ]);

            $movement = FileMovement::create([
                'case_id' => $case->id,
                'barcode_scanned' => $case->permanent_barcode,
                'from_section' => $fromSection,
                'to_section' => $toSection,
                'movement_type' => 'user_handover',
                'received_by_user_id' => $lockedRecipient->id,
                'received_at' => $receivedAt,
                'notes' => $this->movementNotes($batch, $notes),
            ]);

            $item->update([
                'active_case_id' => null,
                'status' => FileTransferItem::STATUS_RECEIVED,
                'received_at' => $receivedAt,
                'received_by_user_id' => $lockedRecipient->id,
                'file_movement_id' => $movement->id,
            ]);

            $this->refreshBatchStatus($batch, $receivedAt);

            return $item->fresh(['batch', 'courtCase', 'receivedBy', 'movement']);
        }, 3);
    }

    public function cancelPendingBatch(User $actor, FileTransferBatch $transferBatch, string $reason): FileTransferBatch
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new FileTransferException('A cancellation reason is required.');
        }

        return DB::transaction(function () use ($actor, $transferBatch, $reason) {
            $batch = FileTransferBatch::query()->whereKey($transferBatch->id)->firstOrFail();
            $isSender = (int) $batch->sender_user_id === (int) $actor->id;

            if (!$isSender && !$actor->hasRole('Super Admin')) {
                throw new FileTransferException('Only the sender or Super Admin can cancel this handover.');
            }

            $pendingCaseIds = FileTransferItem::query()
                ->pending()
                ->where('batch_id', $batch->id)
                ->orderBy('case_id')
                ->pluck('case_id');

            CourtCase::query()
                ->whereIn('id', $pendingCaseIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);

            $pendingItems = FileTransferItem::query()
                ->pending()
                ->where('batch_id', $batch->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $batch = FileTransferBatch::query()->whereKey($batch->id)->lockForUpdate()->firstOrFail();
            $isSender = (int) $batch->sender_user_id === (int) $actor->id;

            if (!$isSender && !$actor->hasRole('Super Admin')) {
                throw new FileTransferException('Only the sender or Super Admin can cancel this handover.');
            }

            if ($pendingItems->isEmpty()) {
                throw new FileTransferException('This handover has no pending files to cancel.');
            }

            $cancelledAt = now();
            FileTransferItem::query()
                ->whereIn('id', $pendingItems->modelKeys())
                ->update([
                    'active_case_id' => null,
                    'status' => FileTransferItem::STATUS_CANCELLED,
                    'cancelled_at' => $cancelledAt,
                    'cancelled_by_user_id' => $actor->id,
                    'cancellation_reason' => $reason,
                    'updated_at' => $cancelledAt,
                ]);

            $batch->update([
                'cancelled_at' => $cancelledAt,
                'cancelled_by_user_id' => $actor->id,
                'cancellation_reason' => $reason,
            ]);
            $this->refreshBatchStatus($batch, $cancelledAt);

            return $batch->fresh(['items', 'cancelledBy']);
        }, 3);
    }

    public function cancelPendingItem(
        User $actor,
        FileTransferBatch $transferBatch,
        FileTransferItem $transferItem,
        string $reason
    ): FileTransferItem {
        $reason = trim($reason);
        if ($reason === '') {
            throw new FileTransferException('A cancellation reason is required.');
        }

        if ((int) $transferItem->batch_id !== (int) $transferBatch->id) {
            throw new FileTransferException('This file does not belong to the selected handover.');
        }

        return DB::transaction(function () use ($actor, $transferBatch, $transferItem, $reason) {
            $itemReference = FileTransferItem::query()->whereKey($transferItem->id)->firstOrFail();

            CourtCase::query()
                ->whereKey($itemReference->case_id)
                ->lockForUpdate()
                ->firstOrFail();

            $item = FileTransferItem::query()->whereKey($itemReference->id)->lockForUpdate()->firstOrFail();
            $batch = FileTransferBatch::query()->whereKey($transferBatch->id)->lockForUpdate()->firstOrFail();
            $isSender = (int) $batch->sender_user_id === (int) $actor->id;

            if (!$isSender && !$actor->hasRole('Super Admin')) {
                throw new FileTransferException('Only the sender or Super Admin can cancel this handover.');
            }

            if ($item->status !== FileTransferItem::STATUS_PENDING || $item->active_case_id === null) {
                throw new FileTransferException('Only a pending file can be cancelled.');
            }

            $cancelledAt = now();
            $item->update([
                'active_case_id' => null,
                'status' => FileTransferItem::STATUS_CANCELLED,
                'cancelled_at' => $cancelledAt,
                'cancelled_by_user_id' => $actor->id,
                'cancellation_reason' => $reason,
            ]);

            $this->refreshBatchStatus($batch, $cancelledAt);

            if ($batch->fresh()->status === FileTransferBatch::STATUS_CANCELLED) {
                $batch->update([
                    'cancelled_at' => $cancelledAt,
                    'cancelled_by_user_id' => $actor->id,
                    'cancellation_reason' => $reason,
                ]);
            }

            return $item->fresh(['batch', 'courtCase', 'cancelledBy']);
        }, 3);
    }

    private function assertEligibleParticipants(?User $sender, ?User $recipient): void
    {
        if (!$sender || !$sender->is_active || !in_array($sender->user_type, ['admin', 'staff'], true)) {
            throw new FileTransferException('The sender account is not eligible to transfer files.');
        }

        if (!$sender->departmentRelation) {
            throw new FileTransferException('Your account is not assigned to a department.');
        }

        if (!$recipient || !$recipient->is_active || !in_array($recipient->user_type, ['admin', 'staff'], true)) {
            throw new FileTransferException('Please select an active staff user.');
        }

        if ((int) $sender->id === (int) $recipient->id) {
            throw new FileTransferException('You cannot send a file to yourself.');
        }

        if (!$recipient->departmentRelation) {
            throw new FileTransferException('The selected user is not assigned to a department.');
        }
    }

    private function sendRestriction(
        ?CourtCase $case,
        User $sender,
        Collection $activeCaseIds,
        Collection $latestMovementTypes
    ): ?string {
        if (!$case) {
            return 'File not found.';
        }

        if (!$case->permanent_barcode || !$case->final_case_number) {
            return 'Complete filing and generate the permanent case number first.';
        }

        if ((int) $case->current_holder_user_id !== (int) $sender->id) {
            return 'You can send only files currently in your custody.';
        }

        if ($activeCaseIds->has($case->id)) {
            return 'This file is already waiting for another user to receive it.';
        }

        if (strcasecmp((string) $case->current_section, 'Court') === 0) {
            return 'A file in court must use the court return workflow.';
        }

        if ($this->caseIsBlocked($case) || $latestMovementTypes->get($case->id) === 'reject') {
            return 'This rejected file cannot be sent.';
        }

        return null;
    }

    private function caseIsBlocked(CourtCase $case): bool
    {
        return in_array(strtolower(trim((string) $case->status)), self::BLOCKED_CASE_STATUSES, true);
    }

    private function latestMovementTypes(Collection $caseIds): Collection
    {
        if ($caseIds->isEmpty()) {
            return collect();
        }

        $latestIds = FileMovement::query()
            ->selectRaw('case_id, MAX(id) as latest_id')
            ->whereIn('case_id', $caseIds)
            ->groupBy('case_id');

        return FileMovement::query()
            ->joinSub($latestIds, 'latest_movement', function ($join) {
                $join->on('file_movements.id', '=', 'latest_movement.latest_id');
            })
            ->pluck('file_movements.movement_type', 'file_movements.case_id');
    }

    private function refreshBatchStatus(FileTransferBatch $batch, $completedAt): void
    {
        $counts = FileTransferItem::query()
            ->where('batch_id', $batch->id)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $pending = (int) $counts->get(FileTransferItem::STATUS_PENDING, 0);
        $received = (int) $counts->get(FileTransferItem::STATUS_RECEIVED, 0);

        if ($pending > 0) {
            $status = $received > 0
                ? FileTransferBatch::STATUS_PARTIALLY_RECEIVED
                : FileTransferBatch::STATUS_PENDING;
            $finishedAt = null;
        } else {
            $status = $received > 0
                ? FileTransferBatch::STATUS_COMPLETED
                : FileTransferBatch::STATUS_CANCELLED;
            $finishedAt = $completedAt;
        }

        $batch->update([
            'status' => $status,
            'completed_at' => $finishedAt,
        ]);
    }

    private function movementNotes(FileTransferBatch $batch, ?string $notes): string
    {
        $message = 'Internal handover '.$batch->batch_no.'.';
        $notes = $this->nullableText($notes);

        return $notes ? $message.' '.$notes : $message;
    }

    private function nullableText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
