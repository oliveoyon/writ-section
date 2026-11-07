<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserPhotoManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_and_replace_a_staff_passport_photo(): void
    {
        Storage::fake('public');

        Role::create(['name' => 'Staff', 'guard_name' => 'web']);
        $superAdminRole = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['user_type' => 'admin']);
        $admin->assignRole($superAdminRole);
        $department = Department::create([
            'name' => 'Scanning',
            'display_name' => 'Scanning',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="photo"', false)
            ->assertSee('assets/img/user1-128x128.jpg', false);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Photo Test User',
                'employee_id' => 'PHOTO-001',
                'login_id' => '9101',
                'email' => 'photo-user@example.test',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'department' => $department->id,
                'user_type' => 'staff',
                'is_active' => '1',
                'photo' => UploadedFile::fake()->image('passport.jpg', 400, 500),
            ])
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'photo-user@example.test')->firstOrFail();
        $oldPath = str_replace('storage/', '', $user->photo);

        $this->assertStringStartsWith('storage/user-photos/', $user->photo);
        Storage::disk('public')->assertExists($oldPath);

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $user))
            ->assertOk()
            ->assertSee($user->photo, false);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'employee_id' => $user->employee_id,
                'login_id' => $user->login_id,
                'email' => $user->email,
                'department' => $department->id,
                'user_type' => 'staff',
                'is_active' => '1',
                'photo' => UploadedFile::fake()->image('replacement.png', 400, 500),
            ])
            ->assertRedirect(route('admin.users.index'));

        $newPath = str_replace('storage/', '', $user->fresh()->photo);

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
        $this->assertNotSame($oldPath, $newPath);
    }

    public function test_user_photo_rejects_non_image_uploads(): void
    {
        Role::create(['name' => 'Staff', 'guard_name' => 'web']);
        $superAdminRole = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['user_type' => 'admin']);
        $admin->assignRole($superAdminRole);
        $department = Department::create(['name' => 'Process Service']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Invalid Photo User',
                'employee_id' => 'PHOTO-002',
                'email' => 'invalid-photo@example.test',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'department' => $department->id,
                'user_type' => 'staff',
                'photo' => UploadedFile::fake()->create('document.pdf', 20, 'application/pdf'),
            ])
            ->assertSessionHasErrors('photo');

        $this->assertDatabaseMissing('users', ['email' => 'invalid-photo@example.test']);
    }
}
