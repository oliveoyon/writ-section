<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View Roles')->only(['index']);
        $this->middleware('permission:Create Role')->only(['store']);
        $this->middleware('permission:Edit Role')->only(['edit', 'update']);
        $this->middleware('permission:Delete Role')->only(['destroy']);
        $this->middleware('permission:Assign Permissions')->only(['permissions', 'assignPermissions']);
    }
    // Show all roles
    public function index()
    {
        $loggedInUser = Auth::user();

        $roles = $loggedInUser->hasRole('Super Admin')
            ? Role::all()
            : Role::where('name', '!=', 'Super Admin')->get();
            
        $permissions = Permission::with('group')->get();

        return view('admin.rbac.roles', compact('roles', 'permissions'));
    }

    // Store new role
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        $role = Role::create($validated);

        return response()->json([
            'success' => true,
            'role' => $role,
            'message' => 'Role created successfully!'
        ]);
    }

    // Edit role
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    // Update role
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', Rule::unique('roles')->ignore($role->id)],
        ]);

        $role->update($validated);

        return response()->json([
            'success' => true,
            'role' => $role,
            'message' => 'Role updated successfully!'
        ]);
    }

    // Delete role
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully!'
        ]);
    }

    // Show permissions for role (assign modal)
    public function permissions($id)
    {
        $role = Role::findOrFail($id);
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        $permissions = Permission::with('group')->get();
        return response()->json([
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions
        ]);
    }

    // Assign permissions to role
    public function assignPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $permissionIds = $request->input('permissions', []);
        $role->syncPermissions($permissionIds);

        return response()->json([
            'success' => true,
            'message' => 'Permissions assigned successfully!'
        ]);
    }
}
