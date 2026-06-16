<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public const LOGIN_ID = 'SUPER-ADMIN';
    public const PASSWORD = 'Pass@1234';

    public function run(): void
    {
        $department = Department::firstOrCreate(['name' => 'Assistant Registrar Office']);
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $user = User::updateOrCreate(
            ['email' => 'super.admin@writ.local'],
            [
                'name' => 'Super Admin',
                'login_id' => self::LOGIN_ID,
                'department' => (string) $department->id,
                'user_type' => 'admin',
                'is_active' => true,
                'password' => Hash::make(self::PASSWORD),
            ]
        );

        $user->syncRoles([$role->name]);
    }
}
