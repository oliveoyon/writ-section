<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PermissionGroup;
use Illuminate\Validation\Rule;

class PermissionGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View Permission Group')->only(['index']);
        $this->middleware('permission:Create Permission Group')->only(['store']);
        $this->middleware('permission:Edit Permission Group')->only(['edit', 'update']);
        $this->middleware('permission:Delete Permission Group')->only(['destroy']);
    }

    public function index()
    {
        $groups = PermissionGroup::withCount('permissions')->get();
        return view('admin.rbac.permission_group', compact('groups'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:permission_groups,name',
        ]);

        $group = PermissionGroup::create($validated);

        return response()->json([
            'success' => true,
            'group' => $group,
            'message' => 'Group created successfully!'
        ]);
    }

    public function edit($id)
    {
        $group = PermissionGroup::findOrFail($id);
        return response()->json($group);
    }

    public function update(Request $request, $id)
    {
        $group = PermissionGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', Rule::unique('permission_groups')->ignore($group->id)],
        ]);

        $group->update($validated);

        return response()->json([
            'success' => true,
            'group' => $group,
            'message' => 'Group updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        $group = PermissionGroup::findOrFail($id);
        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Group deleted successfully!'
        ]);
    }
}
