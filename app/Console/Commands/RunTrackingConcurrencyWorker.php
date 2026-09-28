<?php

namespace App\Console\Commands;

use App\Models\FileTransferBatch;
use App\Models\FileTransferItem;
use App\Models\User;
use App\Services\FileTransferService;
use Illuminate\Console\Command;
use Throwable;

class RunTrackingConcurrencyWorker extends Command
{
    protected $signature = 'tracking:concurrency-worker
        {action : send, receive, or cancel}
        {--actor= : Acting user ID}
        {--recipient= : Recipient user ID for send}
        {--case= : Case ID}
        {--batch= : Transfer batch ID for cancel}
        {--item= : Transfer item ID for cancel}
        {--start-at=0 : Unix timestamp with microseconds used as a start barrier}';

    protected $description = 'Internal worker used by the RTFTS MySQL concurrency audit';

    protected $hidden = true;

    public function handle(FileTransferService $transfers): int
    {
        if (! app()->environment(['local', 'testing', 'staging'])) {
            $this->error('Concurrency worker is disabled outside local, testing, and staging environments.');

            return self::FAILURE;
        }

        $this->waitForBarrier((float) $this->option('start-at'));

        try {
            $actor = User::query()->findOrFail((int) $this->option('actor'));
            $action = (string) $this->argument('action');

            $result = match ($action) {
                'send' => $this->send($transfers, $actor),
                'receive' => $this->receive($transfers, $actor),
                'cancel' => $this->cancel($transfers, $actor),
                default => throw new \InvalidArgumentException('Unsupported concurrency action.'),
            };

            $this->line(json_encode([
                'outcome' => 'success',
                'action' => $action,
                'result' => $result,
            ], JSON_THROW_ON_ERROR));
        } catch (Throwable $exception) {
            $this->line(json_encode([
                'outcome' => 'rejected',
                'action' => (string) $this->argument('action'),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ], JSON_THROW_ON_ERROR));
        }

        return self::SUCCESS;
    }

    private function send(FileTransferService $transfers, User $sender): array
    {
        $recipient = User::query()->findOrFail((int) $this->option('recipient'));
        $result = $transfers->send($sender, $recipient, [(int) $this->option('case')]);

        if ($result['sent']->isEmpty()) {
            throw new \RuntimeException($result['failed'][0]['reason'] ?? 'The file was not sent.');
        }

        return ['batch_id' => $result['batch']?->id];
    }

    private function receive(FileTransferService $transfers, User $recipient): array
    {
        $item = $transfers->receive($recipient, (int) $this->option('case'));

        return ['item_id' => $item->id];
    }

    private function cancel(FileTransferService $transfers, User $actor): array
    {
        $batch = FileTransferBatch::query()->findOrFail((int) $this->option('batch'));
        $item = FileTransferItem::query()->findOrFail((int) $this->option('item'));
        $cancelled = $transfers->cancelPendingItem(
            $actor,
            $batch,
            $item,
            'Concurrency audit cancellation'
        );

        return ['item_id' => $cancelled->id];
    }

    private function waitForBarrier(float $startAt): void
    {
        while ($startAt > 0 && microtime(true) < $startAt) {
            usleep(1000);
        }
    }
}
