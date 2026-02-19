<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View Departments')->only(['index']);
        $this->middleware('permission:Create Departments')->only(['create', 'store']);
        $this->middleware('permission:Edit Departments')->only(['edit', 'update']);
        $this->middleware('permission:Delete Departments')->only(['destroy']);
    }

    public function index()
    {
        $departments = Department::all();
        return view('admin.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:departments,name',
        ]);

        $department = Department::create($validated);

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
            'name' => ['required', Rule::unique('departments')->ignore($department->id)],
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
        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully!'
        ]);
    }
}
