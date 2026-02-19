<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\PermissionGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View Users')->only(['index']);
        $this->middleware('permission:Create Users')->only(['create', 'store']);
        $this->middleware('permission:Edit Users')->only(['edit', 'update']);
        $this->middleware('permission:Delete Users')->only(['destroy']);
        $this->middleware('permission:View User Permissions')->only(['destroy']);
    }

    public function index()
    {
        $users = User::with('roles', 'permissions')->get();
        return view('admin.rbac.users.index', compact('users'));
    }

    public function create()
    {
        $loggedInUser = Auth::user();
        $departments = Department::get();

        $roles = $loggedInUser->hasRole('Super Admin')
            ? Role::all()
            : Role::where('name', '!=', 'Super Admin')->get();

        $permissionGroups = PermissionGroup::with('permissions')->get();

        $userRoles = [];
        $directPermissions = [];

        return view('admin.rbac.users.create_edit', compact(
            'roles',
            'permissionGroups',
            'userRoles',
            'directPermissions',
            'departments'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login_id' => 'nullable|string|max:255|unique:users,login_id',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'department' => 'nullable|exists:departments,id', // validate FK
            'roles' => 'array',
            'permissions' => 'array',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->login_id = $request->login_id;
        $user->email = $request->email;
        $user->department = $request->department; // store department id
        $user->password = Hash::make($request->password);
        $user->is_active = $request->has('is_active');
        $user->user_type = $request->user_type ?? null;
        $user->save();

        $user->assignRole($request->roles ?? []);
        $user->givePermissionTo($request->permissions ?? []);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.users.index')
            ->with('swal-success', 'User created successfully.');
    }


    public function edit(User $user)
    {
        $loggedInUser = Auth::user();
        $departments = Department::get();

        $roles = $loggedInUser->hasRole('Super Admin')
            ? Role::all()
            : Role::where('name', '!=', 'Super Admin')->get();

        $permissionGroups = PermissionGroup::with('permissions')->get();
        $userRoles = $user->roles->pluck('name')->toArray();
        $directPermissions = $user->permissions->pluck('name')->toArray();

        return view('admin.rbac.users.create_edit', compact(
            'user',
            'roles',
            'permissionGroups',
            'userRoles',
            'directPermissions',
            'departments'
        ));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login_id' => 'nullable|string|max:255|unique:users,login_id,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'department' => 'nullable|exists:departments,id', // validate FK
            'roles' => 'array',
            'permissions' => 'array',
        ]);

        $user->name = $request->name;
        $user->login_id = $request->login_id;
        $user->email = $request->email;
        $user->department = $request->department; // store department id
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->is_active = $request->has('is_active');
        $user->user_type = $request->user_type ?? $user->user_type;
        $user->save();

        $user->syncRoles($request->roles ?? []);
        $user->syncPermissions($request->permissions ?? []);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.users.index')
            ->with('swal-success', 'User updated successfully.');
    }


    public function destroy(User $user, Request $request)
    {
        $user->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => 'User deleted successfully']);
        }

        return redirect()->route('admin.users.index')
            ->with('swal-success', 'User deleted successfully.');
    }
}
