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
    public function index(Request $request)
    {
        $activeTab = $request->query('tab') === 'lawyers' ? 'lawyers' : 'users';
        $staffSearch = trim((string) $request->query('staff_q', ''));
        $lawyerSearch = trim((string) $request->query('lawyer_q', ''));
        $lawyerStatus = trim((string) $request->query('lawyer_status', ''));

        $staffUserCount = User::whereIn('user_type', ['admin', 'staff'])->count();
        $lawyerUserCount = User::where('user_type', 'lawyer')->count();
        $users = null;
        $lawyerUsers = null;

        if ($activeTab === 'users') {
            $users = User::query()
                ->select('users.id', 'users.name', 'users.email', 'users.login_id', 'users.department', 'users.user_type', 'users.is_active')
                ->with(['roles', 'departmentRelation:id,name,display_name'])
                ->leftJoin('departments', 'departments.id', '=', 'users.department')
                ->whereIn('users.user_type', ['admin', 'staff'])
                ->when($staffSearch !== '', function ($query) use ($staffSearch) {
                    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $staffSearch) . '%';
                    $query->where(function ($inner) use ($like) {
                        $inner->where('users.name', 'like', $like)
                            ->orWhere('users.email', 'like', $like)
                            ->orWhere('users.login_id', 'like', $like)
                            ->orWhere('departments.name', 'like', $like)
                            ->orWhere('departments.display_name', 'like', $like);
                    });
                })
                ->orderBy('users.name')
                ->paginate(25, ['users.*'], 'staff_page')
                ->withQueryString();
        }

        if ($activeTab === 'lawyers') {
            $lawyerUsers = User::query()
                ->select('users.id', 'users.name', 'users.email', 'users.user_type', 'users.is_active')
                ->with('lawyer:id,user_id,full_name,phone,bar_council_id')
                ->leftJoin('lawyers', 'lawyers.user_id', '=', 'users.id')
                ->where('users.user_type', 'lawyer')
                ->when($lawyerStatus === 'active', fn ($query) => $query->where('users.is_active', true))
                ->when($lawyerStatus === 'inactive', fn ($query) => $query->where('users.is_active', false))
                ->when($lawyerSearch !== '', function ($query) use ($lawyerSearch) {
                    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $lawyerSearch) . '%';
                    $query->where(function ($inner) use ($like) {
                        $inner->where('users.name', 'like', $like)
                            ->orWhere('users.email', 'like', $like)
                            ->orWhere('lawyers.full_name', 'like', $like)
                            ->orWhere('lawyers.phone', 'like', $like)
                            ->orWhere('lawyers.bar_council_id', 'like', $like);
                    });
                })
                ->orderByRaw('COALESCE(lawyers.full_name, users.name)')
                ->paginate(25, ['users.*'], 'lawyer_page')
                ->withQueryString();
        }

        $userTypeLabels = $this->userTypeLabels();

        return view('admin.rbac.users.index', compact(
            'users',
            'lawyerUsers',
            'activeTab',
            'staffUserCount',
            'lawyerUserCount',
            'staffSearch',
            'lawyerSearch',
            'lawyerStatus',
            'userTypeLabels'
        ));
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
        if ($user->user_type === 'lawyer') {
            return redirect()->route('admin.users.index')
                ->withErrors(['user' => 'Lawyer accounts are managed from the Lawyer panel.']);
        }

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

    public function activate(User $user, Request $request)
    {
        $user->forceFill(['is_active' => true])->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => 'User activated successfully']);
        }

        return redirect()->route('admin.users.index')
            ->with('swal-success', 'User activated successfully.');
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
