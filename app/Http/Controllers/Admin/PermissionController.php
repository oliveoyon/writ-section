<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Validation\Rule;



class PermissionController extends Controller
{
    public function __construct()
    {
        // Apply permission middleware to specific methods
        $this->middleware('permission:View Permission')->only(['index']);
        $this->middleware('permission:Create Permission')->only(['store']);
        $this->middleware('permission:Edit Permission')->only(['edit', 'update']);
        $this->middleware('permission:Delete Permission')->only(['destroy']);
    }
    // Show all permissions
    public function index()
    {
        $permissions = Permission::with('group')->get();
        $groups = PermissionGroup::all();
        return view('admin.rbac.permissions', compact('permissions', 'groups'));
    }

    // Store a new permission
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:permissions,name',
            'group_id' => 'required|exists:permission_groups,id',
        ]);

        $permission = Permission::create($validated);

        return response()->json([
            'success' => true,
            'permission' => $permission,
            'message' => 'Permission created successfully!'
        ]);
    }

    // Edit (return JSON for modal)
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return response()->json($permission);
    }

    // Update permission
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', Rule::unique('permissions')->ignore($permission->id)],
            'group_id' => 'required|exists:permission_groups,id',
        ]);

        $permission->update($validated);

        return response()->json([
            'success' => true,
            'permission' => $permission,
            'message' => 'Permission updated successfully!'
        ]);
    }

    // Delete permission
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully!'
        ]);
    }
}
