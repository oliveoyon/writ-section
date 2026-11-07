<?php

namespace Tests\Feature;

use App\Models\CourtCase;
use App\Models\Department;
use App\Models\FileTransferBatch;
use App\Models\FileTransferItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkspaceIdentityBarTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_user_sees_identity_actions_and_live_file_counts(): void
    {
        Role::create(['name' => 'Staff', 'guard_name' => 'web']);
        $department = Department::create([
            'name' => 'Dealing Assistant',
            'display_name' => 'Dealing Assistant Desk',
        ]);
        $recipient = User::factory()->create([
            'name' => 'Visible Working User',
            'photo' => 'storage/user-photos/visible-user.jpg',
            'department' => $department->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);
        $recipient->assignRole('Staff');
        $sender = User::factory()->create([
            'name' => 'Sending User',
            'department' => $department->id,
            'user_type' => 'staff',
        ]);

        foreach (range(1, 4) as $serial) {
            CourtCase::create([
                'entry_source' => 'legacy',
                'status' => 'in_progress',
                'permanent_barcode' => '13202600'.str_pad((string) $serial, 4, '0', STR_PAD_LEFT),
                'final_case_number' => "WRPET {$serial}/2026",
                'current_holder_user_id' => $recipient->id,
            ]);
        }

        $batch = FileTransferBatch::create([
            'batch_no' => 'TRF-BAR-001',
            'sender_user_id' => $sender->id,
            'recipient_user_id' => $recipient->id,
            'sender_name' => $sender->name,
            'sender_section' => 'Filing Section',
            'recipient_name' => $recipient->name,
            'recipient_section' => $department->display_name,
            'status' => FileTransferBatch::STATUS_PENDING,
            'sent_at' => now(),
        ]);

        foreach (range(5, 6) as $serial) {
            $case = CourtCase::create([
                'entry_source' => 'legacy',
                'status' => 'in_progress',
                'permanent_barcode' => '13202600'.str_pad((string) $serial, 4, '0', STR_PAD_LEFT),
                'final_case_number' => "WRPET {$serial}/2026",
                'current_holder_user_id' => $sender->id,
            ]);

            FileTransferItem::create([
                'batch_id' => $batch->id,
                'case_id' => $case->id,
                'active_case_id' => $case->id,
                'status' => FileTransferItem::STATUS_PENDING,
                'sent_at' => now(),
            ]);
        }

        $this->actingAs($recipient)
            ->get(route('admin.tracking.handover.workspace'))
            ->assertOk()
            ->assertSee('Current Working User')
            ->assertSee('Visible Working User')
            ->assertSee('Dealing Assistant Desk')
            ->assertSee('storage/user-photos/visible-user.jpg', false)
            ->assertSee('Receive Files')
            ->assertSee('Send Files')
            ->assertSee('aria-label="2 files waiting"', false)
            ->assertSee('aria-label="4 files in custody"', false);
    }

    public function test_super_admin_keeps_the_administration_header_without_workspace_bar(): void
    {
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $department = Department::create(['name' => 'Assistant Registrar Office']);
        $admin = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'admin',
        ]);
        $admin->assignRole($role);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Current Working User');
    }
}
