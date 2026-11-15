<?php

namespace Tests\Feature;

use App\Models\CourtCase;
use App\Models\User;
use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CleanApplicationDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_cleanup_refuses_to_run_while_site_is_online(): void
    {
        $this->app->instance('env', 'production');
        $maintenance = \Mockery::mock(MaintenanceMode::class);
        $maintenance->shouldReceive('active')->andReturn(false);
        $this->app->instance(MaintenanceMode::class, $maintenance);
        $lawyer = User::factory()->create(['user_type' => 'lawyer']);
        $this->artisan('app:clean-data', ['--force' => true])->assertFailed();
        $this->assertDatabaseHas('users', ['id' => $lawyer->id]);
    }

    public function test_preview_does_not_mutate_and_execution_preserves_staff_access_and_photos(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $staff = User::factory()->create(['user_type' => 'staff', 'photo' => 'storage/user-photos/staff.jpg']);
        $role = Role::create(['name' => 'Staff', 'guard_name' => 'web']);
        $staff->assignRole($role);
        $lawyer = User::factory()->create(['user_type' => 'lawyer', 'photo' => 'storage/user-photos/lawyer.jpg']);
        $lawyer->assignRole($role);
        CourtCase::create(['entry_source' => 'legacy', 'status' => 'in_progress', 'current_holder_user_id' => $staff->id]);
        Storage::disk('local')->put('case_files/attachment.pdf', 'attachment');
        Storage::disk('public')->put('lawyers/profile.jpg', 'photo');
        Storage::disk('public')->put('user-photos/staff.jpg', 'staff');
        Storage::disk('public')->put('user-photos/lawyer.jpg', 'lawyer');
        $before = $staff->fresh()->getRawOriginal();
        // Keep generated-file cleanup isolated from real application storage.
        $generated = Storage::fake('cleanup-generated');
        $local = Storage::disk('local');
        $public = Storage::disk('public');
        Storage::shouldReceive('build')->andReturn($generated);
        Storage::shouldReceive('disk')->with('local')->andReturn($local);
        Storage::shouldReceive('disk')->with('public')->andReturn($public);

        $this->artisan('app:clean-data')->assertSuccessful();
        $this->assertDatabaseCount('cases', 1);
        $this->assertDatabaseCount('users', 2);
        Storage::disk('local')->assertExists('case_files/attachment.pdf');

        $this->artisan('app:clean-data', ['--force' => true])->assertSuccessful();
        $this->assertDatabaseCount('cases', 0);
        $this->assertDatabaseCount('users', 1);
        $this->assertSame($before, $staff->fresh()->getRawOriginal());
        $this->assertTrue($staff->fresh()->hasRole('Staff'));
        $this->assertDatabaseCount('model_has_roles', 1);
        Storage::disk('local')->assertMissing('case_files/attachment.pdf');
        Storage::disk('public')->assertMissing('lawyers/profile.jpg');
        Storage::disk('public')->assertMissing('user-photos/lawyer.jpg');
        Storage::disk('public')->assertExists('user-photos/staff.jpg');
        $this->artisan('app:clean-data', ['--force' => true])->assertSuccessful();
    }
}
