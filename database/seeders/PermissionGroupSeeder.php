<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\PermissionGroup;

class PermissionGroupSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create permission groups
        $rbacMgmt = PermissionGroup::firstOrCreate(['name' => 'RBAC Management']);
        $userMgmt = PermissionGroup::firstOrCreate(['name' => 'User Management']);

        // 2. Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // 3. Define permissions per group
        $permissions = [
            $rbacMgmt->id => ['view permission groups', 'create permission groups', 'edit permission groups', 'delete permission groups',
                              'view permissions', 'create permissions', 'edit permissions', 'delete permissions',
                              'view roles', 'create roles', 'edit roles', 'delete roles', 'assign roles & permissions'],
            $userMgmt->id => ['view users', 'create users', 'edit users', 'delete users'],
        ];

        // 4. Create permissions and assign to admin role
        foreach ($permissions as $groupId => $perms) {
            foreach ($perms as $permName) {
                $permission = Permission::firstOrCreate([
                    'name' => $permName,
                    'group_id' => $groupId,
                ]);

                // Assign all permissions to admin role
                $adminRole->givePermissionTo($permission);
            }
        }
    }
}
