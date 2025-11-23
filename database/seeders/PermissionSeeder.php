<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Example roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Define permissions with groups
        $permissions = [
            'User Management' => [
                'view users',
                'create users',
                'edit users',
                'delete users',
            ],
            'Product Management' => [
                'view products',
                'create products',
                'edit products',
                'delete products',
            ],
        ];

        // Create permissions and assign group
        foreach ($permissions as $group => $perms) {
            foreach ($perms as $perm) {
                $permission = Permission::create([
                    'name' => $perm,
                    'group' => $group,
                ]);

                // Optionally assign to admin role
                $adminRole->givePermissionTo($permission);
            }
        }
    }
}
