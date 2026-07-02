<?php

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('departments:sync {--dry-run : Show missing departments without creating}', function () {
    $existing = Department::pluck('name')->all();
    $missing = array_values(array_diff(Department::CANONICAL_NAMES, $existing));

    if (empty($missing)) {
        $this->info('All canonical departments already exist.');
        return self::SUCCESS;
    }

    $this->warn('Missing departments:');
    foreach ($missing as $name) {
        $this->line('- ' . $name);
    }

    if ($this->option('dry-run')) {
        $this->comment('Dry run enabled. No changes were made.');
        return self::SUCCESS;
    }

    foreach ($missing as $name) {
        Department::create(['name' => $name]);
    }

    $this->info('Missing departments created successfully.');
    return self::SUCCESS;
})->purpose('Ensure all required writ-tracking departments exist');

Artisan::command('tracking:audit-users {--show-ok : Include users who are fully ready}', function () {
    $rows = [];
    $users = User::with(['departmentRelation', 'roles'])->orderBy('id')->get();
    $canonical = Department::CANONICAL_NAMES;

    foreach ($users as $user) {
        $departmentName = $user->departmentRelation?->name;
        $issues = [];
        $userType = strtolower((string) $user->user_type);
        $roleNames = $user->roles->pluck('name')->all();
        $roleNamesLower = $user->roles->pluck('name')->map(fn ($r) => strtolower((string) $r))->all();
        $isSuperAdmin = in_array('super admin', $roleNamesLower, true);
        $isAdminRole = in_array('admin', $roleNamesLower, true);
        $isStaffRole = in_array('staff', $roleNamesLower, true);

        if ($userType === 'lawyer') {
            if ($isAdminRole || $isStaffRole || $isSuperAdmin) {
                $issues[] = 'Lawyer should not have admin/staff/super-admin role';
            }
            $isReady = empty($issues);

            if (!$isReady || $this->option('show-ok')) {
                $rows[] = [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->user_type ?? '-',
                    $user->login_id ?? '-',
                    $user->is_active ? 'yes' : 'no',
                    $departmentName ?? '-',
                    implode(', ', $roleNames) ?: '-',
                    $isReady ? 'OK' : implode('; ', $issues),
                ];
            }

            continue;
        }

        if (!in_array($userType, ['admin', 'staff'], true)) {
            $issues[] = 'Unsupported user_type: ' . ($user->user_type ?? 'null');
        }

        $requiresCardAndDepartment = !$isSuperAdmin;

        if ($requiresCardAndDepartment && !$user->login_id) {
            $issues[] = 'Missing card login_id';
        }

        if (!$user->is_active) {
            $issues[] = 'Inactive';
        }

        if ($userType === 'staff') {
            if (!$departmentName) {
                $issues[] = 'No department assigned';
            } elseif (!in_array($departmentName, $canonical, true)) {
                $issues[] = 'Unknown department: ' . $departmentName;
            }
        }

        if ($userType === 'admin' && !($isAdminRole || $isSuperAdmin)) {
            $issues[] = 'Admin user must have Admin or Super Admin role';
        }

        if ($userType === 'staff' && $isSuperAdmin) {
            $issues[] = 'Staff user cannot have Super Admin role';
        }

        if ($userType === 'staff' && !$isStaffRole) {
            $issues[] = 'Staff user must have Staff role';
        }

        $isReady = empty($issues);

        if (!$isReady || $this->option('show-ok')) {
            $rows[] = [
                $user->id,
                $user->name,
                $user->email,
                $user->user_type ?? '-',
                $user->login_id ?? '-',
                $user->is_active ? 'yes' : 'no',
                $departmentName ?? '-',
                implode(', ', $roleNames) ?: '-',
                $isReady ? 'OK' : implode('; ', $issues),
            ];
        }
    }

    if (empty($rows)) {
        $this->info('No issues found. Use --show-ok to print all users.');
        return self::SUCCESS;
    }

    $this->table(
        ['ID', 'Name', 'Email', 'Type', 'Card ID', 'Active', 'Department', 'Roles', 'Status'],
        $rows
    );

    $issueCount = collect($rows)->filter(fn ($row) => $row[8] !== 'OK')->count();
    if ($issueCount > 0) {
        $this->warn("Audit finished with {$issueCount} user(s) needing fixes.");
    } else {
        $this->info('Audit finished with no issues.');
    }

    return self::SUCCESS;
})->purpose('Audit proximity-login readiness and section assignment for users');

Artisan::command('access:sync-user-roles {--apply : Apply changes. Without this flag, performs dry run only}', function () {
    $users = User::with('roles')->orderBy('id')->get();
    $changes = [];

    foreach ($users as $user) {
        $type = strtolower((string) $user->user_type);
        $current = $user->roles->pluck('name')->all();
        $target = null;

        if ($type === 'staff') {
            $target = 'Staff';
        } elseif ($type === 'admin') {
            $hasSuperAdmin = collect($current)->contains(fn ($r) => strtolower((string) $r) === 'super admin');
            $target = $hasSuperAdmin ? 'Super Admin' : 'Admin';
        } elseif ($type === 'lawyer') {
            $target = null;
        }

        if ($target === null) {
            if (!empty($current)) {
                $changes[] = [$user, $current, []];
            }
            continue;
        }

        $isAlreadyCorrect = count($current) === 1 && strtolower($current[0]) === strtolower($target);
        if (!$isAlreadyCorrect) {
            $changes[] = [$user, $current, [$target]];
        }
    }

    if (empty($changes)) {
        $this->info('No role-sync changes required.');
        return self::SUCCESS;
    }

    $rows = [];
    foreach ($changes as [$user, $from, $to]) {
        $rows[] = [
            $user->id,
            $user->name,
            $user->user_type ?? '-',
            implode(', ', $from) ?: '-',
            implode(', ', $to) ?: '-',
        ];
    }
    $this->table(['ID', 'Name', 'Type', 'Current Roles', 'Target Roles'], $rows);

    if (!$this->option('apply')) {
        $this->comment('Dry run only. Re-run with --apply to persist role changes.');
        return self::SUCCESS;
    }

    foreach ($changes as [$user, $from, $to]) {
        $user->syncRoles($to);
    }

    $this->info('Role mapping applied successfully.');
    return self::SUCCESS;
})->purpose('Sync user roles to policy: admin->Admin/Super Admin, staff->Staff, lawyer->no admin/staff roles');

Artisan::command('tracking:health-check', function () {
    $checks = [];

    $requiredMigrations = [
        '2026_02_25_000001_add_tracking_columns_to_cases_table',
        '2026_02_25_000002_make_lawyer_id_nullable_in_cases_table',
        '2026_02_25_000003_create_file_movements_table',
    ];

    $migrationTableExists = Schema::hasTable('migrations');
    $ranMigrations = $migrationTableExists
        ? \Illuminate\Support\Facades\DB::table('migrations')->pluck('migration')->all()
        : [];
    $missingMigrations = array_values(array_diff($requiredMigrations, $ranMigrations));
    $checks[] = ['Migrations', empty($missingMigrations) ? 'PASS' : 'FAIL', empty($missingMigrations) ? '-' : implode(', ', $missingMigrations)];

    $missingDepartments = array_values(array_diff(Department::CANONICAL_NAMES, Department::pluck('name')->all()));
    $checks[] = ['Departments', empty($missingDepartments) ? 'PASS' : 'FAIL', empty($missingDepartments) ? '-' : implode(', ', $missingDepartments)];

    $trackingRoutes = collect(\Illuminate\Support\Facades\Route::getRoutes()->getRoutesByName())
        ->keys()
        ->filter(fn ($name) => str_starts_with($name, 'admin.tracking.'))
        ->values()
        ->all();
    $checks[] = ['Tracking Routes', count($trackingRoutes) >= 9 ? 'PASS' : 'FAIL', count($trackingRoutes) . ' found'];

    $proximityRoute = \Illuminate\Support\Facades\Route::has('proximity.login');
    $checks[] = ['Proximity Route', $proximityRoute ? 'PASS' : 'FAIL', $proximityRoute ? '-' : 'Missing route: proximity.login'];

    $users = User::with(['roles', 'departmentRelation'])->get();
    $invalidUsers = $users->filter(function ($user) {
        $type = strtolower((string) $user->user_type);
        $roles = $user->roles->pluck('name')->map(fn ($r) => strtolower((string) $r))->all();
        $isSuperAdmin = in_array('super admin', $roles, true);
        $isAdmin = in_array('admin', $roles, true);
        $isStaff = in_array('staff', $roles, true);

        if ($type === 'lawyer') {
            return $isSuperAdmin || $isAdmin || $isStaff;
        }

        if ($type === 'admin') {
            if (!($isSuperAdmin || $isAdmin)) {
                return true;
            }
            if (!$isSuperAdmin && !$user->login_id) {
                return true;
            }
            return false;
        }

        if ($type === 'staff') {
            if (!$isStaff || $isSuperAdmin) {
                return true;
            }
            if (!$user->login_id) {
                return true;
            }
            $departmentName = $user->departmentRelation?->name;
            if (!$departmentName || !in_array($departmentName, Department::CANONICAL_NAMES, true)) {
                return true;
            }
            return false;
        }

        return true;
    })->count();
    $checks[] = ['User Mapping', $invalidUsers === 0 ? 'PASS' : 'FAIL', $invalidUsers === 0 ? '-' : "{$invalidUsers} user(s) need fixes"];

    $this->table(['Check', 'Status', 'Details'], $checks);

    $failed = collect($checks)->where('1', 'FAIL')->count();
    if ($failed > 0) {
        $this->warn("Health check finished with {$failed} failing check(s).");
        return self::FAILURE;
    }

    $this->info('Health check passed.');
    return self::SUCCESS;
})->purpose('Run one-command readiness checks for writ-tracking workflow');
