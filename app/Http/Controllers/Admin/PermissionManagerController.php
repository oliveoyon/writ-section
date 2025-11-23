<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PermissionGroup;
use App\Models\Permission;

class PermissionManagerController extends Controller
{
    public function index()
    {
        $groups = PermissionGroup::withCount('permissions')->get();
        return view('admin.rbac.permission_manager', compact('groups'));
    }

    public function groupPermissions($groupId)
    {
        $permissions = Permission::where('group_id', $groupId)->get();
        return response()->json($permissions);
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permission_groups,name'
        ]);
        $group = PermissionGroup::create(['name'=>$request->name]);
        return response()->json(['success'=>true, 'group'=>$group]);
    }

    public function updateGroup(Request $request, $id)
    {
        $group = PermissionGroup::findOrFail($id);
        $request->validate([
            'name' => 'required|unique:permission_groups,name,'.$group->id
        ]);
        $group->update(['name'=>$request->name]);
        return response()->json(['success'=>true, 'group'=>$group]);
    }

    public function destroyGroup($id)
    {
        $group = PermissionGroup::findOrFail($id);
        $group->delete();
        return response()->json(['success'=>true]);
    }

    public function storePermission(Request $request)
    {
        $request->validate([
            'name'=>'required|unique:permissions,name',
            'group_id'=>'required|exists:permission_groups,id'
        ]);
        $perm = Permission::create($request->only('name','group_id'));
        return response()->json(['success'=>true, 'permission'=>$perm]);
    }

    public function updatePermission(Request $request, $id)
    {
        $perm = Permission::findOrFail($id);
        $request->validate([
            'name'=>'required|unique:permissions,name,'.$perm->id,
            'group_id'=>'required|exists:permission_groups,id'
        ]);
        $perm->update($request->only('name','group_id'));
        return response()->json(['success'=>true, 'permission'=>$perm]);
    }

    public function destroyPermission($id)
    {
        $perm = Permission::findOrFail($id);
        $perm->delete();
        return response()->json(['success'=>true]);
    }
}
