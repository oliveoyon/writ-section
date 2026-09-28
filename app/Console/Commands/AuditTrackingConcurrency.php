<?php

namespace App\Console\Commands;

use App\Models\CourtCase;
use App\Models\Department;
use App\Models\FileMovement;
use App\Models\FileTransferBatch;
use App\Models\FileTransferItem;
use App\Models\User;
use App\Services\FileTransferService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class AuditTrackingConcurrency extends Command
{
    protected $signature = 'tracking:concurrency-audit';

    protected $description = 'Run destructive-to-temporary-data MySQL races against RTFTS custody workflows';

    private string $token;

    private array $departmentIds = [];

    private array $userIds = [];

    private array $caseIds = [];

    public function handle(FileTransferService $transfers): int
    {
        if (! app()->environment(['local', 'testing', 'staging'])) {
            $this->error('Concurrency audit is disabled outside local, testing, and staging environments.');

            return self::FAILURE;
        }

        if (DB::getDriverName() !== 'mysql') {
            $this->error('Concurrency audit requires MySQL/InnoDB. Current driver: '.DB::getDriverName());

            return self::FAILURE;
        }

        $this->token = 'AUDIT-'.Str::upper(Str::random(10));
        $this->info('Running isolated MySQL concurrency audit '.$this->token.'...');

        try {
            [$sender, $recipientA, $recipientB] = $this->createParticipants();

            $this->auditDoubleSend($sender, $recipientA, $recipientB);
            $this->auditDoubleReceive($transfers, $sender, $recipientA);
            $this->auditReceiveAgainstCancel($transfers, $sender, $recipientA);

            $this->newLine();
            $this->info('PASS: all custody invariants held under overlapping MySQL requests.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->newLine();
            $this->error('FAIL: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            $this->cleanup();
        }
    }

    private function auditDoubleSend(User $sender, User $recipientA, User $recipientB): void
    {
        $case = $this->createCase($sender, 1);
        $results = $this->race([
            ['send', '--actor='.$sender->id, '--recipient='.$recipientA->id, '--case='.$case->id],
            ['send', '--actor='.$sender->id, '--recipient='.$recipientB->id, '--case='.$case->id],
        ]);

        $this->assertOutcomeCounts($results, 1, 1, 'double send');
        $this->assertSameValue(1, FileTransferItem::query()->where('case_id', $case->id)->pending()->count(), 'double send active item count');
        $this->assertSameValue(1, FileTransferBatch::query()->whereHas('items', fn ($query) => $query->where('case_id', $case->id))->count(), 'double send batch count');
        $this->line('  [PASS] Two simultaneous sends produced one pending handover.');
    }

    private function auditDoubleReceive(FileTransferService $transfers, User $sender, User $recipient): void
    {
        $case = $this->createCase($sender, 2);
        $transfers->send($sender, $recipient, [$case->id]);
        $results = $this->race([
            ['receive', '--actor='.$recipient->id, '--case='.$case->id],
            ['receive', '--actor='.$recipient->id, '--case='.$case->id],
        ]);

        $this->assertOutcomeCounts($results, 1, 1, 'double receive');
        $this->assertSameValue($recipient->id, $case->fresh()->current_holder_user_id, 'double receive holder');
        $this->assertSameValue(1, FileMovement::query()->where('case_id', $case->id)->where('movement_type', 'user_handover')->count(), 'double receive movement count');
        $this->assertSameValue(1, FileTransferItem::query()->where('case_id', $case->id)->where('status', FileTransferItem::STATUS_RECEIVED)->count(), 'double receive item count');
        $this->line('  [PASS] Two simultaneous receipts changed custody and history exactly once.');
    }

    private function auditReceiveAgainstCancel(FileTransferService $transfers, User $sender, User $recipient): void
    {
        $case = $this->createCase($sender, 3);
        $batch = $transfers->send($sender, $recipient, [$case->id])['batch'];
        $item = $batch->items()->firstOrFail();
        $results = $this->race([
            ['receive', '--actor='.$recipient->id, '--case='.$case->id],
            ['cancel', '--actor='.$sender->id, '--batch='.$batch->id, '--item='.$item->id],
        ]);

        $this->assertOutcomeCounts($results, 1, 1, 'receive versus cancellation');
        $item->refresh();
        $case->refresh();
        $movementCount = FileMovement::query()->where('case_id', $case->id)->where('movement_type', 'user_handover')->count();

        if ($item->status === FileTransferItem::STATUS_RECEIVED) {
            $this->assertSameValue($recipient->id, $case->current_holder_user_id, 'receive/cancel holder after receipt');
            $this->assertSameValue(1, $movementCount, 'receive/cancel movement after receipt');
        } elseif ($item->status === FileTransferItem::STATUS_CANCELLED) {
            $this->assertSameValue($sender->id, $case->current_holder_user_id, 'receive/cancel holder after cancellation');
            $this->assertSameValue(0, $movementCount, 'receive/cancel movement after cancellation');
        } else {
            throw new RuntimeException('receive versus cancellation left the item in '.$item->status.' status.');
        }

        $this->assertSameValue(null, $item->active_case_id, 'receive/cancel active case marker');
        $this->line('  [PASS] Receipt racing cancellation produced one consistent final state.');
    }

    private function race(array $commands): array
    {
        $startAt = microtime(true) + 1.25;
        $processes = collect($commands)->map(function (array $arguments) use ($startAt) {
            $process = new Process([
                PHP_BINARY,
                base_path('artisan'),
                'tracking:concurrency-worker',
                ...$arguments,
                '--start-at='.$startAt,
                '--no-ansi',
            ], base_path(), null, null, 30);
            $process->start();

            return $process;
        });

        return $processes->map(function (Process $process): array {
            $process->wait();
            $lines = array_values(array_filter(preg_split('/\R/', trim($process->getOutput())) ?: []));
            $payload = json_decode((string) end($lines), true);

            if (! is_array($payload) || ! isset($payload['outcome'])) {
                throw new RuntimeException('Concurrency worker returned invalid output: '.trim($process->getErrorOutput().' '.$process->getOutput()));
            }

            return $payload;
        })->all();
    }

    private function assertOutcomeCounts(array $results, int $successes, int $rejections, string $scenario): void
    {
        $actualSuccesses = collect($results)->where('outcome', 'success')->count();
        $actualRejections = collect($results)->where('outcome', 'rejected')->count();

        if ($actualSuccesses !== $successes || $actualRejections !== $rejections) {
            throw new RuntimeException($scenario.' returned unexpected outcomes: '.json_encode($results));
        }
    }

    private function assertSameValue(mixed $expected, mixed $actual, string $label): void
    {
        if ($expected !== $actual) {
            throw new RuntimeException($label.' expected '.var_export($expected, true).', got '.var_export($actual, true).'.');
        }
    }

    private function createParticipants(): array
    {
        $departments = collect(['Sender', 'Recipient A', 'Recipient B'])->map(function (string $label) {
            $department = Department::create([
                'name' => $this->token.' '.$label,
                'display_name' => $this->token.' '.$label,
            ]);
            $this->departmentIds[] = $department->id;

            return $department;
        });

        return $departments->values()->map(function (Department $department, int $index) {
            $user = User::create([
                'name' => $this->token.' User '.($index + 1),
                'email' => strtolower($this->token).'-'.($index + 1).'@audit.invalid',
                'employee_id' => 'AUD'.substr($this->token, -6).$index,
                'password' => bcrypt(Str::random(32)),
                'department' => $department->id,
                'user_type' => 'staff',
                'is_active' => true,
            ]);
            $this->userIds[] = $user->id;

            return $user;
        })->all();
    }

    private function createCase(User $holder, int $offset): CourtCase
    {
        $serial = 900000 + random_int(1000, 9000) + $offset;
        $case = CourtCase::create([
            'entry_source' => 'benchmark',
            'case_type' => 'Concurrency Audit',
            'status' => 'in_progress',
            'permanent_barcode' => '132099'.str_pad((string) $serial, 6, '0', STR_PAD_LEFT),
            'final_case_number' => 'WRPET '.$serial.'/2099',
            'current_section' => $holder->departmentRelation->name,
            'current_holder_user_id' => $holder->id,
            'current_holder_at' => now(),
        ]);
        $this->caseIds[] = $case->id;

        return $case;
    }

    private function cleanup(): void
    {
        if (! isset($this->token)) {
            return;
        }

        DB::transaction(function () {
            $batchIds = FileTransferBatch::query()
                ->whereIn('sender_user_id', $this->userIds)
                ->orWhereIn('recipient_user_id', $this->userIds)
                ->pluck('id');

            FileTransferItem::query()->whereIn('batch_id', $batchIds)->delete();
            FileTransferBatch::query()->whereIn('id', $batchIds)->delete();
            FileMovement::query()->whereIn('case_id', $this->caseIds)->delete();
            CourtCase::query()->whereIn('id', $this->caseIds)->delete();
            User::query()->whereIn('id', $this->userIds)->delete();
            Department::query()->whereIn('id', $this->departmentIds)->delete();
        });

        $this->line('Audit records removed.');
    }
}
