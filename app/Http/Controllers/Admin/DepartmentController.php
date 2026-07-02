<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::all();
        $roles = Role::orderBy('id')->get();

        return view('admin.departments.index', compact('departments', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:departments,name',
        ]);

        $department = Department::create([
            'name' => $validated['name'],
            'display_name' => $validated['name'],
        ]);

        return response()->json([
            'success' => true,
            'department' => $department,
            'message' => 'Department added successfully!'
        ]);
    }

    public function edit($id)
    {
        $department = Department::findOrFail($id);
        return response()->json($department);
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:125'],
        ]);

        $department->update($validated);

        return response()->json([
            'success' => true,
            'department' => $department,
            'message' => 'Department updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        if (in_array($department->name, Department::CANONICAL_NAMES, true)) {
            return response()->json([
                'success' => false,
                'message' => 'System departments cannot be deleted. You may change the display name instead.',
            ], 422);
        }

        if (User::where('department', $department->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This department is assigned to users and cannot be deleted.',
            ], 422);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully!'
        ]);
    }
}
