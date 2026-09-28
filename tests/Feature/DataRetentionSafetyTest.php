<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\CourtCase;
use App\Models\CourtDispatchBatch;
use App\Models\Department;
use App\Models\FileMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DataRetentionSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_movement_history_cannot_be_edited_or_deleted_through_the_model(): void
    {
        $case = CourtCase::create(['status' => 'filed']);
        $movement = FileMovement::create([
            'case_id' => $case->id,
            'barcode_scanned' => '132026000001',
            'to_section' => 'Filing Section',
            'movement_type' => 'receive',
            'received_at' => now(),
        ]);

        try {
            $movement->update(['notes' => 'Changed']);
            $this->fail('Movement update should have been blocked.');
        } catch (\LogicException $exception) {
            $this->assertSame('File movement history is append-only.', $exception->getMessage());
        }

        $this->expectException(\LogicException::class);
        $movement->delete();
    }

    public function test_admin_user_action_deactivates_user_instead_of_deleting_record(): void
    {
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['user_type' => 'admin']);
        $admin->assignRole($role);
        $staff = User::factory()->create(['user_type' => 'staff', 'is_active' => true]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $staff))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'is_active' => false,
        ]);
    }

    public function test_last_active_super_admin_cannot_be_deactivated(): void
    {
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create(['user_type' => 'admin', 'is_active' => true]);
        $superAdmin->assignRole($role);
        $this->actingAs($superAdmin)
            ->delete(route('admin.users.destroy', $superAdmin))
            ->assertSessionHasErrors('user');

        $this->assertTrue((bool) $superAdmin->fresh()->is_active);
    }

    public function test_used_court_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['user_type' => 'admin']);
        $admin->assignRole($role);
        $court = Court::create([
            'name_en' => 'Used Court',
            'name_bn' => null,
            'code' => 'USED-01',
            'is_active' => true,
        ]);
        CourtDispatchBatch::create([
            'batch_no' => 'BATCH-001',
            'court_id' => $court->id,
            'created_by_user_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.courts.destroy', $court))
            ->assertUnprocessable();

        $this->assertDatabaseHas('courts', ['id' => $court->id]);
        $this->assertDatabaseHas('court_dispatch_batches', ['court_id' => $court->id]);
    }

    public function test_non_draft_case_cannot_be_deleted_from_model(): void
    {
        $case = CourtCase::create([
            'entry_source' => 'filing',
            'status' => 'filed',
            'subject' => 'Historical case',
        ]);

        $this->expectException(\LogicException::class);

        $case->delete();
    }

    public function test_operational_department_user_cannot_be_created_as_admin(): void
    {
        Role::create(['name' => 'Staff', 'guard_name' => 'web']);
        $superAdminRole = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['user_type' => 'admin']);
        $admin->assignRole($superAdminRole);
        $filing = Department::create([
            'name' => 'Filing Section',
            'display_name' => 'Filing Section',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Filing User',
                'employee_id' => 'EMP-FILING-001',
                'login_id' => 'FILING-USER',
                'email' => 'filing@example.test',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'department' => $filing->id,
                'user_type' => 'admin',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'filing@example.test')->firstOrFail();

        $this->assertSame('staff', $user->user_type);
        $this->assertTrue($user->hasRole('Staff'));
        $this->assertFalse($user->hasRole('Admin'));

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.tracking.handover.workspace'));
    }
}
