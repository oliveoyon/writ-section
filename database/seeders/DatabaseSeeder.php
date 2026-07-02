<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public const SUPER_ADMIN_LOGIN_ID = 'SUPER-ADMIN';
    public const SUPER_ADMIN_EMAIL = 'super.admin@writ.local';
    public const SUPER_ADMIN_PASSWORD = 'Pass@1234';

    public function run(): void
    {
        DB::transaction(function () {
            foreach (Department::CANONICAL_NAMES as $name) {
                Department::firstOrCreate(
                    ['name' => $name],
                    ['display_name' => $name]
                );
            }

            $roles = collect(['Super Admin', 'Admin', 'Staff'])
                ->mapWithKeys(function (string $name) {
                    $role = Role::firstOrCreate([
                        'name' => $name,
                        'guard_name' => 'web',
                    ]);

                    if (!$role->display_name) {
                        $role->forceFill(['display_name' => $name])->save();
                    }

                    return [$name => $role];
                });

            $department = Department::where('name', 'Assistant Registrar Office')->firstOrFail();
            $user = User::firstOrNew(['email' => self::SUPER_ADMIN_EMAIL]);

            if (!$user->exists) {
                $user->password = Hash::make(self::SUPER_ADMIN_PASSWORD);
            }

            $user->forceFill([
                'name' => 'Super Admin',
                'login_id' => self::SUPER_ADMIN_LOGIN_ID,
                'department' => (string) $department->id,
                'user_type' => 'admin',
                'is_active' => true,
            ])->save();

            $user->syncRoles([$roles->get('Super Admin')]);
        });
    }
}
