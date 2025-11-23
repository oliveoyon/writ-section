<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        $roles = $loggedInUser->hasRole('Super Admin')
            ? Role::all()
            : Role::where('name', '!=', 'Super Admin')->get();

        $permissionGroups = PermissionGroup::with('permissions')->get();

        // Empty arrays for new user
        $userRoles = [];
        $directPermissions = [];

        return view('admin.rbac.users.create_edit', compact(
            'roles',
            'permissionGroups',
            'userRoles',
            'directPermissions'
        ));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'array',
            'permissions' => 'array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        if ($request->permissions) {
            $user->givePermissionTo($request->permissions);
        }

        // Clear Spatie permission cache for instant sync
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        if ($request->expectsJson()) {
            return response()->json(['success' => 'User created successfully']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $loggedInUser = Auth::user();

        $roles = $loggedInUser->hasRole('Super Admin')
            ? Role::all()
            : Role::where('name', '!=', 'Super Admin')->get();

        $permissionGroups = PermissionGroup::with('permissions')->get();
        $userRoles = $user->roles->pluck('name')->toArray();

        // Only direct permissions
        $directPermissions = $user->permissions->pluck('name')->toArray();

        return view('admin.rbac.users.create_edit', compact(
            'user',
            'roles',
            'permissionGroups',
            'userRoles',
            'directPermissions'
        ));
    }


    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'array',
            'permissions' => 'array',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Sync roles
        $user->syncRoles($request->roles ?? []);

        // Sync direct permissions
        $user->syncPermissions($request->permissions ?? []);

        // Clear Spatie permission cache for instant sync
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        if ($request->expectsJson()) {
            return response()->json(['success' => 'User updated successfully']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user, Request $request)
    {
        $user->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => 'User deleted successfully']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }

    
}
