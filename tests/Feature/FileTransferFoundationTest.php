<?php

namespace Tests\Feature;

use App\Models\CourtCase;
use App\Models\Department;
use App\Models\FileTransferBatch;
use App\Models\FileTransferItem;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileTransferFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_keeps_historical_snapshots_and_relationships(): void
    {
        [$sender, $recipient, $case] = $this->transferParticipants();
        $sentAt = now()->subMinutes(15)->startOfSecond();

        $batch = $this->createBatch($sender, $recipient, $sentAt);
        $item = FileTransferItem::create([
            'batch_id' => $batch->id,
            'case_id' => $case->id,
            'active_case_id' => $case->id,
            'status' => FileTransferItem::STATUS_PENDING,
            'sent_at' => $sentAt,
        ]);

        $this->assertTrue($batch->sender->is($sender));
        $this->assertTrue($batch->recipient->is($recipient));
        $this->assertTrue($item->courtCase->is($case));
        $this->assertTrue($case->fresh()->activeTransferItem->is($item));
        $this->assertSame($sender->name, $batch->sender_name);
        $this->assertSame($recipient->departmentRelation->name, $batch->recipient_section);
    }

    public function test_a_case_cannot_have_two_active_transfers(): void
    {
        [$sender, $recipient, $case] = $this->transferParticipants();
        $sentAt = now()->startOfSecond();

        foreach (['TRF-TEST-0001', 'TRF-TEST-0002'] as $index => $batchNo) {
            $batch = $this->createBatch($sender, $recipient, $sentAt, $batchNo);

            if ($index === 1) {
                $this->expectException(QueryException::class);
            }

            FileTransferItem::create([
                'batch_id' => $batch->id,
                'case_id' => $case->id,
                'active_case_id' => $case->id,
                'status' => FileTransferItem::STATUS_PENDING,
                'sent_at' => $sentAt,
            ]);
        }
    }

    public function test_completed_transfer_releases_case_for_a_future_transfer(): void
    {
        [$sender, $recipient, $case] = $this->transferParticipants();
        $sentAt = now()->subMinutes(10)->startOfSecond();
        $firstBatch = $this->createBatch($sender, $recipient, $sentAt, 'TRF-TEST-0003');
        $firstItem = FileTransferItem::create([
            'batch_id' => $firstBatch->id,
            'case_id' => $case->id,
            'active_case_id' => $case->id,
            'status' => FileTransferItem::STATUS_PENDING,
            'sent_at' => $sentAt,
        ]);

        $firstItem->update([
            'active_case_id' => null,
            'status' => FileTransferItem::STATUS_RECEIVED,
            'received_at' => $sentAt->copy()->addMinutes(10),
            'received_by_user_id' => $recipient->id,
        ]);

        $secondBatch = $this->createBatch($recipient, $sender, now(), 'TRF-TEST-0004');
        $secondItem = FileTransferItem::create([
            'batch_id' => $secondBatch->id,
            'case_id' => $case->id,
            'active_case_id' => $case->id,
            'status' => FileTransferItem::STATUS_PENDING,
            'sent_at' => now(),
        ]);

        $this->assertSame(600, $firstItem->fresh()->receiptDelayInSeconds());
        $this->assertTrue($case->fresh()->activeTransferItem->is($secondItem));
        $this->assertCount(2, $case->fresh()->transferItems);
    }

    private function transferParticipants(): array
    {
        $senderDepartment = Department::create([
            'name' => 'Typing Section',
            'display_name' => 'Typing Section',
        ]);
        $recipientDepartment = Department::create([
            'name' => 'Compare Section',
            'display_name' => 'Compare Section',
        ]);
        $sender = User::factory()->create([
            'name' => 'Sender User',
            'employee_id' => '1001',
            'department' => $senderDepartment->id,
            'user_type' => 'staff',
        ]);
        $recipient = User::factory()->create([
            'name' => 'Recipient User',
            'employee_id' => '1002',
            'department' => $recipientDepartment->id,
            'user_type' => 'staff',
        ]);
        $case = CourtCase::create([
            'entry_source' => 'lawyer',
            'case_type' => 'Service Matter',
            'status' => 'in_progress',
            'permanent_barcode' => '132026004788',
            'final_case_number' => 'WRPET 4788/2026',
            'current_section' => $senderDepartment->name,
            'current_holder_user_id' => $sender->id,
            'current_holder_at' => now(),
        ]);

        return [$sender, $recipient, $case];
    }

    private function createBatch(
        User $sender,
        User $recipient,
        $sentAt,
        string $batchNo = 'TRF-TEST-0000'
    ): FileTransferBatch {
        return FileTransferBatch::create([
            'batch_no' => $batchNo,
            'sender_user_id' => $sender->id,
            'recipient_user_id' => $recipient->id,
            'sender_department_id' => $sender->department,
            'recipient_department_id' => $recipient->department,
            'sender_name' => $sender->name,
            'sender_employee_id' => $sender->employee_id,
            'sender_section' => $sender->departmentRelation->name,
            'recipient_name' => $recipient->name,
            'recipient_employee_id' => $recipient->employee_id,
            'recipient_section' => $recipient->departmentRelation->name,
            'status' => FileTransferBatch::STATUS_PENDING,
            'sent_at' => $sentAt,
        ]);
    }
}
