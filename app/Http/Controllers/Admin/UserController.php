<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'departmentRelation'])->get();
        $userTypeLabels = $this->userTypeLabels();

        return view('admin.rbac.users.index', compact('users', 'userTypeLabels'));
    }

    public function create()
    {
        $departments = Department::get();
        $userTypeLabels = $this->userTypeLabels();

        return view('admin.rbac.users.create_edit', compact('departments', 'userTypeLabels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login_id' => 'nullable|string|max:255|unique:users,login_id',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'department' => 'nullable|exists:departments,id', // validate FK
            'user_type' => 'required|in:admin,staff',
        ]);

        $userType = $this->resolveUserTypeForDepartment(
            $request->input('user_type'),
            $request->input('department')
        );

        $user = new User();
        $user->name = $request->name;
        $user->login_id = $request->login_id;
        $user->email = $request->email;
        $user->department = $request->department; // store department id
        $user->password = Hash::make($request->password);
        $user->is_active = $request->has('is_active');
        $user->user_type = $userType;
        $user->save();

        $roles = $this->resolveRolesForUserType($userType);
        $user->syncRoles($roles);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.users.index')
            ->with('swal-success', 'User created successfully.');
    }


    public function edit(User $user)
    {
        $departments = Department::get();
        $userTypeLabels = $this->userTypeLabels();

        return view('admin.rbac.users.create_edit', compact('user', 'departments', 'userTypeLabels'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login_id' => 'nullable|string|max:255|unique:users,login_id,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'department' => 'nullable|exists:departments,id', // validate FK
            'user_type' => 'required|in:admin,staff',
        ]);

        $willBeActive = $request->has('is_active');
        $isSuperAdmin = $user->hasRole('Super Admin');
        $userType = $this->resolveUserTypeForDepartment(
            $request->input('user_type'),
            $request->input('department')
        );

        if (
            $isSuperAdmin &&
            (!$willBeActive || $userType !== 'admin') &&
            $this->activeSuperAdminCount() <= 1
        ) {
            return back()->withErrors([
                'user_type' => 'The last active Super Admin cannot be deactivated or changed to staff.',
            ])->withInput();
        }

        $user->name = $request->name;
        $user->login_id = $request->login_id;
        $user->email = $request->email;
        $user->department = $request->department; // store department id
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->is_active = $willBeActive;
        $user->user_type = $userType;
        $user->save();

        $roles = $isSuperAdmin && $userType === 'admin'
            ? ['Super Admin']
            : $this->resolveRolesForUserType($userType);
        $user->syncRoles($roles);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.users.index')
            ->with('swal-success', 'User updated successfully.');
    }


    public function destroy(User $user, Request $request)
    {
        if (Auth::id() === $user->id) {
            return $this->deactivationBlocked($request, 'You cannot deactivate your own account from user management.');
        }

        if ($user->hasRole('Super Admin') && $user->is_active && $this->activeSuperAdminCount() <= 1) {
            return $this->deactivationBlocked($request, 'The last active Super Admin cannot be deactivated.');
        }

        $user->forceFill([
            'is_active' => false,
            'remember_token' => null,
        ])->save();

        DB::table('sessions')->where('user_id', $user->id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => 'User deactivated successfully']);
        }

        return redirect()->route('admin.users.index')
            ->with('swal-success', 'User deactivated successfully.');
    }

    private function resolveRolesForUserType(string $userType): array
    {
        if ($userType === 'staff') {
            return [Role::whereRaw('LOWER(name) = ?', ['staff'])->value('name') ?? 'Staff'];
        }

        return [Role::whereRaw('LOWER(name) = ?', ['admin'])->value('name') ?? 'Admin'];
    }

    private function resolveUserTypeForDepartment(string $requestedType, $departmentId): string
    {
        $departmentName = Department::whereKey($departmentId)->value('name');

        if ($departmentName !== 'Assistant Registrar Office') {
            return 'staff';
        }

        return $requestedType === 'admin' ? 'admin' : 'staff';
    }

    private function userTypeLabels(): array
    {
        $roles = Role::whereIn('name', ['Admin', 'Staff'])
            ->get()
            ->keyBy(fn (Role $role) => strtolower($role->name));

        return [
            'admin' => $roles->get('admin')?->display_name ?: 'Admin',
            'staff' => $roles->get('staff')?->display_name ?: 'Staff',
        ];
    }

    private function activeSuperAdminCount(): int
    {
        return User::role('Super Admin')->where('is_active', true)->count();
    }

    private function deactivationBlocked(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return redirect()->route('admin.users.index')->withErrors(['user' => $message]);
    }

}
