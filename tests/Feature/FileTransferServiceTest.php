<?php

namespace Tests\Feature;

use App\Exceptions\FileTransferException;
use App\Models\CourtCase;
use App\Models\Court;
use App\Models\Department;
use App\Models\FileTransferBatch;
use App\Models\FileTransferItem;
use App\Models\User;
use App\Services\FileTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileTransferServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_workspace_and_recipient_selection_are_available(): void
    {
        [$sender, $recipient, $case, $department] = $this->participants();
        User::factory()->create([
            'name' => 'Inactive Recipient',
            'department' => $department->id,
            'user_type' => 'staff',
            'is_active' => false,
        ]);

        $this->actingAs($sender)
            ->get(route('admin.tracking.handover.workspace'))
            ->assertOk()
            ->assertSee('Send Files')
            ->assertSee('Receive Files');

        $this->actingAs($sender)
            ->get(route('admin.tracking.handover.recipients'))
            ->assertOk()
            ->assertSee($recipient->name)
            ->assertDontSee('Inactive Recipient');

        app(FileTransferService::class)->send($sender, $recipient, [$case->id]);

        $this->actingAs($recipient)
            ->get(route('admin.tracking.handover.workspace'))
            ->assertOk()
            ->assertSee('Files Waiting for You')
            ->assertSee($sender->name)
            ->assertSee('1 file');

        $this->actingAs($sender)
            ->get(route('admin.tracking.handover.workspace'))
            ->assertOk()
            ->assertSee('Waiting for Receipt')
            ->assertSee($recipient->name)
            ->assertSee('1 not received');
    }

    public function test_send_screen_validates_and_creates_a_pending_handover(): void
    {
        [$sender, $recipient, $case] = $this->participants();

        $this->actingAs($sender)
            ->getJson(route('admin.tracking.handover.validate', [
                'recipient' => $recipient,
                'identifier' => $case->final_case_number,
            ]))
            ->assertOk()
            ->assertJson([
                'valid' => true,
                'case_id' => $case->id,
                'case_number' => $case->final_case_number,
            ]);

        $this->actingAs($sender)
            ->post(route('admin.tracking.handover.store', $recipient), [
                'case_ids' => [$case->id],
            ])
            ->assertRedirect(route('admin.tracking.handover.create', $recipient));

        $this->assertDatabaseHas('file_transfer_items', [
            'case_id' => $case->id,
            'active_case_id' => $case->id,
            'status' => FileTransferItem::STATUS_PENDING,
        ]);
    }

    public function test_current_holder_can_send_and_selected_recipient_can_receive(): void
    {
        [$sender, $recipient, $case] = $this->participants();
        $service = app(FileTransferService::class);

        $result = $service->send($sender, $recipient, [$case->id], 'Routine handover');
        $batch = $result['batch'];

        $this->assertNotNull($batch);
        $this->assertMatchesRegularExpression('/^TRF-\d{8}-\d{8}$/', $batch->batch_no);
        $this->assertCount(1, $result['sent']);
        $this->assertSame([], $result['failed']);
        $this->assertSame($sender->id, $case->fresh()->current_holder_user_id);
        $this->assertDatabaseHas('file_transfer_items', [
            'batch_id' => $batch->id,
            'case_id' => $case->id,
            'active_case_id' => $case->id,
            'status' => FileTransferItem::STATUS_PENDING,
        ]);

        $item = $service->receive($recipient, $case->id);

        $this->assertSame(FileTransferItem::STATUS_RECEIVED, $item->status);
        $this->assertNull($item->active_case_id);
        $this->assertSame($recipient->id, $case->fresh()->current_holder_user_id);
        $this->assertSame($recipient->departmentRelation->name, $case->fresh()->current_section);
        $this->assertSame(FileTransferBatch::STATUS_COMPLETED, $batch->fresh()->status);
        $this->assertDatabaseHas('file_movements', [
            'case_id' => $case->id,
            'movement_type' => 'user_handover',
            'received_by_user_id' => $recipient->id,
        ]);
    }

    public function test_another_user_cannot_receive_the_pending_file(): void
    {
        [$sender, $recipient, $case, $department] = $this->participants();
        $otherUser = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);
        $service = app(FileTransferService::class);
        $service->send($sender, $recipient, [$case->id]);

        $this->expectException(FileTransferException::class);
        $this->expectExceptionMessage('This file was sent to another user.');

        $service->receive($otherUser, $case->id);
    }

    public function test_sender_can_cancel_pending_files_and_release_the_case(): void
    {
        [$sender, $recipient, $case] = $this->participants();
        $service = app(FileTransferService::class);
        $batch = $service->send($sender, $recipient, [$case->id])['batch'];

        $cancelled = $service->cancelPendingBatch($sender, $batch, 'Selected the wrong recipient');

        $this->assertSame(FileTransferBatch::STATUS_CANCELLED, $cancelled->status);
        $this->assertDatabaseHas('file_transfer_items', [
            'batch_id' => $batch->id,
            'case_id' => $case->id,
            'active_case_id' => null,
            'status' => FileTransferItem::STATUS_CANCELLED,
            'cancelled_by_user_id' => $sender->id,
            'cancellation_reason' => 'Selected the wrong recipient',
        ]);
        $this->assertSame($sender->id, $case->fresh()->current_holder_user_id);
    }

    public function test_sender_can_cancel_one_pending_file_without_cancelling_the_remaining_batch(): void
    {
        [$sender, $recipient, $firstCase] = $this->participants();
        $secondCase = $this->createCase($sender, '132026004790', 'WRPET 4790/2026');
        $service = app(FileTransferService::class);
        $batch = $service->send($sender, $recipient, [$firstCase->id, $secondCase->id])['batch'];
        $item = $batch->items()->where('case_id', $firstCase->id)->firstOrFail();

        $service->cancelPendingItem($sender, $batch, $item, 'File should remain with sender');

        $this->assertDatabaseHas('file_transfer_items', [
            'id' => $item->id,
            'active_case_id' => null,
            'status' => FileTransferItem::STATUS_CANCELLED,
            'cancelled_by_user_id' => $sender->id,
            'cancellation_reason' => 'File should remain with sender',
        ]);
        $this->assertDatabaseHas('file_transfer_items', [
            'batch_id' => $batch->id,
            'case_id' => $secondCase->id,
            'active_case_id' => $secondCase->id,
            'status' => FileTransferItem::STATUS_PENDING,
        ]);
        $this->assertSame(FileTransferBatch::STATUS_PENDING, $batch->fresh()->status);
    }

    public function test_recipient_cannot_cancel_a_pending_handover(): void
    {
        [$sender, $recipient, $case] = $this->participants();
        $service = app(FileTransferService::class);
        $batch = $service->send($sender, $recipient, [$case->id])['batch'];

        $this->expectException(FileTransferException::class);
        $this->expectExceptionMessage('Only the sender or Super Admin can cancel this handover.');

        $service->cancelPendingBatch($recipient, $batch, 'Not authorized');
    }

    public function test_received_file_cannot_be_cancelled(): void
    {
        [$sender, $recipient, $case] = $this->participants();
        $service = app(FileTransferService::class);
        $batch = $service->send($sender, $recipient, [$case->id])['batch'];
        $item = $batch->items()->firstOrFail();
        $service->receive($recipient, $case->id);

        $this->expectException(FileTransferException::class);
        $this->expectExceptionMessage('Only a pending file can be cancelled.');

        $service->cancelPendingItem($sender, $batch, $item, 'Too late');
    }

    public function test_pending_and_completed_handovers_are_visible_in_tracking_views_and_report(): void
    {
        [$sender, $recipient, $case] = $this->participants();
        $registrarDepartment = Department::create([
            'name' => 'Registrar',
            'display_name' => 'Registrar',
        ]);
        $registrar = User::factory()->create([
            'department' => $registrarDepartment->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);
        $service = app(FileTransferService::class);
        $batch = $service->send($sender, $recipient, [$case->id])['batch'];

        $this->actingAs($registrar)
            ->get(route('admin.tracking.lookup', ['q' => $case->final_case_number]))
            ->assertOk()
            ->assertSee('Waiting for')
            ->assertSee($recipient->name);

        $this->actingAs($registrar)
            ->get(route('admin.tracking.timeline', $case))
            ->assertOk()
            ->assertSee('Waiting for Receipt')
            ->assertSee($batch->batch_no);

        $service->receive($recipient, $case->id);

        $this->actingAs($registrar)
            ->get(route('admin.tracking.timeline', $case))
            ->assertOk()
            ->assertSee('Internal Handover')
            ->assertSee('Time Taken')
            ->assertSee($batch->batch_no);

        $this->actingAs($sender)
            ->get(route('admin.tracking.register-report', ['movement_type' => 'user_handover']))
            ->assertOk()
            ->assertSee('Internal Handover')
            ->assertSee($sender->name)
            ->assertSee($recipient->name)
            ->assertSee('Time taken');
    }

    public function test_file_cannot_be_sent_to_court_without_custody_or_during_pending_handover(): void
    {
        $department = Department::create(['name' => 'Office Assistant', 'display_name' => 'Office Assistant']);
        $holder = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);
        $otherOperator = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);
        $recipient = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);
        $court = Court::create([
            'name_en' => 'Test Court',
            'name_bn' => 'Test Court',
            'code' => 'GUARD-COURT',
            'is_active' => true,
        ]);
        $case = $this->createCase($holder, '132026004791', 'WRPET 4791/2026');

        $this->actingAs($otherOperator)
            ->post(route('admin.tracking.court.dispatch.store'), [
                'court_id' => $court->id,
                'barcodes' => $case->permanent_barcode,
            ])
            ->assertSessionHas('court_failed', fn (array $failed): bool =>
                $failed[0]['reason'] === 'You can send only files currently in your custody.'
            );

        app(FileTransferService::class)->send($holder, $recipient, [$case->id]);

        $this->actingAs($holder)
            ->post(route('admin.tracking.court.dispatch.store'), [
                'court_id' => $court->id,
                'barcodes' => $case->permanent_barcode,
            ])
            ->assertSessionHas('court_failed', fn (array $failed): bool =>
                str_contains($failed[0]['reason'], 'Cancel the handover first')
            );

        $this->assertSame($holder->id, $case->fresh()->current_holder_user_id);
        $this->assertDatabaseMissing('file_movements', [
            'case_id' => $case->id,
            'movement_type' => 'dispatch_to_court',
        ]);
    }

    public function test_pending_handover_blocks_rejection_and_registrar_override(): void
    {
        $affidavit = Department::create(['name' => 'Affidavit Section', 'display_name' => 'Affidavit Section']);
        $destination = Department::create(['name' => 'Record Room', 'display_name' => 'Record Room']);
        $holder = User::factory()->create([
            'department' => $affidavit->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);
        $recipient = User::factory()->create([
            'department' => $destination->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);
        $case = $this->createCase($holder, '132026004792', 'WRPET 4792/2026');
        app(FileTransferService::class)->send($holder, $recipient, [$case->id]);

        $this->actingAs($holder)
            ->post(route('admin.tracking.section.receive.store'), [
                'action' => 'reject',
                'barcode' => $case->permanent_barcode,
                'reason' => 'Test rejection',
            ])
            ->assertSessionHas('error', fn (string $message): bool =>
                str_contains($message, 'Cancel the handover before rejecting it')
            );

        $registrarDepartment = Department::create([
            'name' => 'Assistant Registrar Office',
            'display_name' => 'Assistant Registrar Office',
        ]);
        $registrar = User::factory()->create([
            'department' => $registrarDepartment->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);

        $this->actingAs($registrar)
            ->post(route('admin.tracking.override', $case), [
                'to_department_id' => $destination->id,
                'reason' => 'incorrect_section',
            ])
            ->assertSessionHas('error', fn (string $message): bool =>
                str_contains($message, 'Cancel that handover before using Registrar Override')
            );

        $this->assertSame($holder->id, $case->fresh()->current_holder_user_id);
        $this->assertDatabaseMissing('file_movements', ['case_id' => $case->id]);
    }

    public function test_send_processes_owned_files_and_reports_unowned_files_without_creating_extra_batches(): void
    {
        [$sender, $recipient, $ownedCase, $department] = $this->participants();
        $anotherHolder = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);
        $unownedCase = $this->createCase($anotherHolder, '132026004789', 'WRPET 4789/2026');
        $service = app(FileTransferService::class);

        $result = $service->send($sender, $recipient, [$ownedCase->id, $unownedCase->id]);

        $this->assertCount(1, $result['sent']);
        $this->assertCount(1, $result['failed']);
        $this->assertSame('You can send only files currently in your custody.', $result['failed'][0]['reason']);
        $this->assertSame(1, FileTransferBatch::count());
        $this->assertSame(1, FileTransferItem::count());
    }

    private function participants(): array
    {
        $department = Department::create([
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
            'department' => $department->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);
        $recipient = User::factory()->create([
            'name' => 'Recipient User',
            'employee_id' => '1002',
            'department' => $recipientDepartment->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);
        $case = $this->createCase($sender, '132026004788', 'WRPET 4788/2026');

        return [$sender, $recipient, $case, $department];
    }

    private function createCase(User $holder, string $barcode, string $caseNumber): CourtCase
    {
        return CourtCase::create([
            'entry_source' => 'lawyer',
            'case_type' => 'Service Matter',
            'status' => 'in_progress',
            'permanent_barcode' => $barcode,
            'final_case_number' => $caseNumber,
            'current_section' => $holder->departmentRelation->name,
            'current_holder_user_id' => $holder->id,
            'current_holder_at' => now(),
        ]);
    }
}
