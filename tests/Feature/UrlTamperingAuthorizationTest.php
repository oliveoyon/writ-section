<?php

namespace Tests\Feature;

use App\Models\CourtCase;
use App\Models\Department;
use App\Models\FileMovement;
use App\Models\Lawyer;
use App\Models\User;
use App\Services\FileTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UrlTamperingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_admin_cannot_open_super_admin_management_urls(): void
    {
        Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['user_type' => 'admin']);
        $admin->assignRole('Admin');

        foreach ([
            route('admin.users.index'),
            route('admin.users.create'),
            route('admin.users.card-labels'),
            route('admin.departments.index'),
            route('admin.courts.index'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertForbidden();
        }
    }

    public function test_staff_cannot_open_admin_management_url_by_typing_it(): void
    {
        $department = Department::create(['name' => 'Typing Section', 'display_name' => 'Typing Section']);
        $staff = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($staff)
            ->get(route('admin.users.index'))
            ->assertRedirect(route('admin.tracking.handover.workspace'));
    }

    public function test_staff_cannot_tamper_with_department_restricted_workflow_urls(): void
    {
        $department = Department::create(['name' => 'Typing Section', 'display_name' => 'Typing Section']);
        $staff = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
        ]);
        $case = CourtCase::create([
            'entry_source' => 'lawyer',
            'case_type' => 'Service Matter',
            'status' => 'filed',
            'permanent_barcode' => '132026009901',
            'final_case_number' => 'WRPET 9901/2026',
            'current_section' => 'Typing Section',
            'current_holder_user_id' => $staff->id,
        ]);

        foreach ([
            route('admin.tracking.filing.scan-temp'),
            route('admin.tracking.filing.direct-create'),
            route('admin.tracking.court.dispatch.index'),
            route('admin.tracking.court.return.index'),
            route('admin.tracking.timeline', $case),
        ] as $url) {
            $this->actingAs($staff)
                ->get($url)
                ->assertRedirect(route('admin.tracking.handover.workspace'));
        }
    }

    public function test_unrelated_user_cannot_view_or_cancel_another_users_handover(): void
    {
        $department = Department::create(['name' => 'Typing Section', 'display_name' => 'Typing Section']);
        $sender = $this->staff($department, 'Sender');
        $recipient = $this->staff($department, 'Recipient');
        $outsider = $this->staff($department, 'Outsider');
        $case = CourtCase::create([
            'entry_source' => 'lawyer',
            'case_type' => 'Service Matter',
            'status' => 'in_progress',
            'permanent_barcode' => '132026009902',
            'final_case_number' => 'WRPET 9902/2026',
            'current_section' => $department->name,
            'current_holder_user_id' => $sender->id,
        ]);

        $batch = app(FileTransferService::class)->send($sender, $recipient, [$case->id])['batch'];
        $item = $batch->items()->firstOrFail();

        $this->actingAs($outsider)
            ->get(route('admin.tracking.handover.show', $batch))
            ->assertForbidden();
        $this->actingAs($outsider)
            ->post(route('admin.tracking.handover.cancel', $batch), ['reason' => 'tampered'])
            ->assertForbidden();
        $this->actingAs($outsider)
            ->post(route('admin.tracking.handover.item.cancel', [$batch, $item]), ['reason' => 'tampered'])
            ->assertForbidden();

        $this->assertSame(0, (int) $item->fresh()->status);
    }

    public function test_old_case_url_cannot_take_an_existing_file_from_another_holder(): void
    {
        $source = Department::create(['name' => 'Typing Section', 'display_name' => 'Typing Section']);
        $destination = Department::create(['name' => 'Compare Section', 'display_name' => 'Compare Section']);
        $holder = $this->staff($source, 'Current Holder');
        $scanner = $this->staff($destination, 'Scanner');
        $case = CourtCase::create([
            'entry_source' => 'legacy',
            'case_type' => 'Constitutional Matter',
            'status' => 'in_progress',
            'permanent_barcode' => '132026009903',
            'final_case_number' => 'WRPET 9903/2026',
            'final_case_year' => '2026',
            'registration_serial' => 9903,
            'current_section' => $source->name,
            'current_holder_user_id' => $holder->id,
        ]);

        $this->actingAs($scanner)
            ->post(route('admin.tracking.old-case-receive.store'), [
                'identifier' => $case->permanent_barcode,
            ])
            ->assertSessionHas('error');

        $this->assertSame($holder->id, $case->fresh()->current_holder_user_id);
        $this->assertSame($source->name, $case->fresh()->current_section);
        $this->assertSame(0, FileMovement::where('case_id', $case->id)->count());
    }

    public function test_staff_edit_endpoint_cannot_be_used_to_rewrite_a_lawyer_account(): void
    {
        $superAdmin = $this->superAdmin();
        $lawyerUser = User::factory()->create([
            'name' => 'Original Lawyer',
            'email' => 'original-lawyer@example.test',
            'user_type' => 'lawyer',
            'is_active' => true,
        ]);
        Lawyer::create([
            'user_id' => $lawyerUser->id,
            'bar_council_id' => 'SCB-TAMPER-1',
            'full_name' => $lawyerUser->name,
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin)
            ->put(route('admin.users.update', $lawyerUser), [
                'name' => 'Tampered Name',
                'employee_id' => 'TAMPERED',
                'email' => 'tampered@example.test',
                'password' => 'ChangedPassword123',
                'password_confirmation' => 'ChangedPassword123',
                'user_type' => 'admin',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.users.index', ['tab' => 'lawyers']))
            ->assertSessionHasErrors('user');

        $lawyerUser->refresh();
        $this->assertSame('Original Lawyer', $lawyerUser->name);
        $this->assertSame('lawyer', $lawyerUser->user_type);
        $this->assertSame('original-lawyer@example.test', $lawyerUser->email);
    }

    public function test_staff_cannot_expand_register_report_scope_with_query_parameters(): void
    {
        $ownDepartment = Department::create(['name' => 'Typing Section', 'display_name' => 'Typing Section']);
        $otherDepartment = Department::create(['name' => 'Compare Section', 'display_name' => 'Compare Section']);
        $staff = $this->staff($ownDepartment, 'Report User');

        $ownCase = CourtCase::create([
            'entry_source' => 'legacy',
            'status' => 'in_progress',
            'permanent_barcode' => '132026009904',
            'final_case_number' => 'WRPET 9904/2026',
        ]);
        $otherCase = CourtCase::create([
            'entry_source' => 'legacy',
            'status' => 'in_progress',
            'permanent_barcode' => '132026009905',
            'final_case_number' => 'WRPET 9905/2026',
        ]);
        FileMovement::create([
            'case_id' => $ownCase->id,
            'barcode_scanned' => $ownCase->permanent_barcode,
            'from_section' => 'Filing Section',
            'to_section' => $ownDepartment->name,
            'movement_type' => 'receive',
            'received_by_user_id' => $staff->id,
            'received_at' => now(),
            'notes' => 'OWN-REPORT-MARKER',
        ]);
        FileMovement::create([
            'case_id' => $otherCase->id,
            'barcode_scanned' => $otherCase->permanent_barcode,
            'from_section' => 'Filing Section',
            'to_section' => $otherDepartment->name,
            'movement_type' => 'receive',
            'received_at' => now(),
            'notes' => 'OTHER-REPORT-MARKER',
        ]);

        $this->actingAs($staff)
            ->get(route('admin.tracking.register-report', [
                'filter_mode' => 'date_range',
                'date_from' => now()->format('d-m-Y'),
                'date_to' => now()->format('d-m-Y'),
                'section' => $otherDepartment->name,
                'movement_scope' => 'all',
            ]))
            ->assertOk()
            ->assertSee('WRPET 9904/2026')
            ->assertDontSee('WRPET 9905/2026');
    }

    private function staff(Department $department, string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'department' => $department->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);
    }

    private function superAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['user_type' => 'admin', 'is_active' => true]);
        $user->assignRole($role);

        return $user;
    }
}
