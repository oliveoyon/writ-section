<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public const SUPER_ADMIN_LOGIN_ID = null;
    public const SUPER_ADMIN_EMPLOYEE_ID = '0000';
    public const SUPER_ADMIN_EMAIL = 'super.admin@writ.local';
    public const SUPER_ADMIN_PASSWORD = 'password';

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ([
                'court_dispatch_batch_items',
                'file_movements',
                'case_files',
                'case_petitioners',
                'case_respondents',
                'cases',
                'court_dispatch_batches',
                'lawyers',
                'model_has_permissions',
                'model_has_roles',
                'password_reset_tokens',
                'sessions',
                'users',
                'case_registration_sequences',
            ] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

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
                'employee_id' => self::SUPER_ADMIN_EMPLOYEE_ID,
                'department' => (string) $department->id,
                'user_type' => 'admin',
                'is_active' => true,
            ])->save();

            $user->syncRoles([$roles->get('Super Admin')]);
        });
    }
}
