<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OperationalHealthCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_without_optional_card_id_passes_user_readiness_audit(): void
    {
        $department = Department::create([
            'name' => Department::CANONICAL_NAMES[0],
            'display_name' => Department::CANONICAL_NAMES[0],
        ]);
        $staffRole = Role::create(['name' => 'Staff', 'guard_name' => 'web']);
        $user = User::factory()->create([
            'employee_id' => '8801',
            'login_id' => null,
            'department' => $department->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);
        $user->assignRole($staffRole);

        $this->artisan('tracking:audit-users')
            ->expectsOutput('No issues found. Use --show-ok to print all users.')
            ->assertSuccessful();
    }
}
