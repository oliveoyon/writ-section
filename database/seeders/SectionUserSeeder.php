<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SectionUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedWithPassword('Pass@1234', false);
    }

    public function seedWithPassword(string $plainPassword, bool $forceResetPassword = false): array
    {
        // one user per departments table row (id 1..11)
        $users = [
            [
                'name' => 'Assistant Registrar',
                'email' => 'assistant.registrar@writ.local',
                'login_id' => 'CARD-AR-0001',
                'department_name' => 'Assistant Registrar Office',
                'user_type' => 'admin',
                'role' => 'Admin',
            ],
            [
                'name' => 'Office Assistant',
                'email' => 'office.assistant@writ.local',
                'login_id' => 'CARD-OFF-0001',
                'department_name' => 'Office Assistant',
                'user_type' => 'staff',
                'role' => 'Staff',
            ],
            [
                'name' => 'Filing Operator',
                'email' => 'filing.section@writ.local',
                'login_id' => 'CARD-FIL-0001',
                'department_name' => 'Filing Section',
                'user_type' => 'staff',
                'role' => 'Staff',
            ],
            [
                'name' => 'Affidavit Operator',
                'email' => 'affidavit.section@writ.local',
                'login_id' => 'CARD-AFF-0001',
                'department_name' => 'Affidavit Section',
                'user_type' => 'staff',
                'role' => 'Staff',
            ],
            [
                'name' => 'Requisite Operator',
                'email' => 'requisite.section@writ.local',
                'login_id' => 'CARD-REQ-0001',
                'department_name' => 'Requisite Section',
                'user_type' => 'staff',
                'role' => 'Staff',
            ],
            [
                'name' => 'Put-Up Operator',
                'email' => 'putup.section@writ.local',
                'login_id' => 'CARD-PUT-0001',
                'department_name' => 'Put-Up Section',
                'user_type' => 'staff',
                'role' => 'Staff',
            ],
            [
                'name' => 'Typing Operator',
                'email' => 'typing.section@writ.local',
                'login_id' => 'CARD-TYP-0001',
                'department_name' => 'Typing Section',
                'user_type' => 'staff',
                'role' => 'Staff',
            ],
            [
                'name' => 'Compare Operator',
                'email' => 'compare.section@writ.local',
                'login_id' => 'CARD-CMP-0001',
                'department_name' => 'Compare Section',
                'user_type' => 'staff',
                'role' => 'Staff',
            ],
            [
                'name' => 'Superintendent Operator',
                'email' => 'superintendent@writ.local',
                'login_id' => 'CARD-SUP-0001',
                'department_name' => 'Superintendent',
                'user_type' => 'staff',
                'role' => 'Staff',
            ],
            [
                'name' => 'Ready Table Operator',
                'email' => 'ready.table@writ.local',
                'login_id' => 'CARD-RDY-0001',
                'department_name' => 'Ready Table',
                'user_type' => 'staff',
                'role' => 'Staff',
            ],
            [
                'name' => 'Record Room Operator',
                'email' => 'record.room@writ.local',
                'login_id' => 'CARD-RRM-0001',
                'department_name' => 'Record Room',
                'user_type' => 'staff',
                'role' => 'Staff',
            ],
        ];

        $adminRole = $this->resolveRoleName('Admin');
        $staffRole = $this->resolveRoleName('Staff');

        $result = [];

        foreach ($users as $row) {
            $department = Department::firstOrCreate(['name' => $row['department_name']]);

            $user = User::firstOrNew(['email' => $row['email']]);
            $user->name = $row['name'];
            $user->email = $row['email'];
            $user->login_id = $row['login_id'];

            // department column stores departments.id
            $user->department = (string) $department->id;

            $user->user_type = $row['user_type'];
            $user->is_active = true;

            if (!$user->exists || $forceResetPassword) {
                $user->password = Hash::make($plainPassword);
            }

            $user->save();

            $user->syncRoles([
                $row['role'] === 'Admin' ? $adminRole : $staffRole
            ]);

            $result[] = [
                'name' => $user->name,
                'email' => $user->email,
                'login_id' => $user->login_id,
                'department_id' => (int) $department->id,
                'department_name' => $department->name,
                'user_type' => $user->user_type,
                'role' => $user->roles()->pluck('name')->implode(', '),
            ];
        }

        // Legacy account cleanup: keep historical references intact but disable login.
        User::query()
            ->where('email', 'court.operator@writ.local')
            ->update(['is_active' => false]);

        return $result;
    }

    private function resolveRoleName(string $preferred): string
    {
        return Role::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($preferred)])
            ->value('name') ?? $preferred;
    }
}
