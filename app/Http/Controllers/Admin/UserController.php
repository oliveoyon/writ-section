<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'departmentRelation'])->get();
        return view('admin.rbac.users.index', compact('users'));
    }

    public function create()
    {
        $departments = Department::get();
        return view('admin.rbac.users.create_edit', compact('departments'));
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
            'face_descriptor' => 'nullable|string',
        ]);

        $faceDescriptor = $this->parseFaceDescriptor($request->input('face_descriptor'));
        if ($request->filled('face_descriptor') && $faceDescriptor === null) {
            return back()->withErrors(['face_descriptor' => 'Invalid face descriptor payload.'])->withInput();
        }

        $user = new User();
        $user->name = $request->name;
        $user->login_id = $request->login_id;
        $user->email = $request->email;
        $user->department = $request->department; // store department id
        $user->password = Hash::make($request->password);
        $user->is_active = $request->has('is_active');
        $user->user_type = $request->user_type;
        $user->face_descriptor = $faceDescriptor;
        $user->save();

        $roles = $this->resolveRolesForUserType($request->input('user_type'));
        $user->syncRoles($roles);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.users.index')
            ->with('swal-success', 'User created successfully.');
    }


    public function edit(User $user)
    {
        $departments = Department::get();
        return view('admin.rbac.users.create_edit', compact('user', 'departments'));
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
            'face_descriptor' => 'nullable|string',
        ]);

        $faceDescriptor = $this->parseFaceDescriptor($request->input('face_descriptor'));
        if ($request->filled('face_descriptor') && $faceDescriptor === null) {
            return back()->withErrors(['face_descriptor' => 'Invalid face descriptor payload.'])->withInput();
        }

        $user->name = $request->name;
        $user->login_id = $request->login_id;
        $user->email = $request->email;
        $user->department = $request->department; // store department id
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->is_active = $request->has('is_active');
        $user->user_type = $request->user_type;
        $user->face_descriptor = $faceDescriptor;
        $user->save();

        $roles = $this->resolveRolesForUserType($request->input('user_type'));
        $user->syncRoles($roles);

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

    private function resolveRolesForUserType(string $userType): array
    {
        if ($userType === 'staff') {
            return [Role::whereRaw('LOWER(name) = ?', ['staff'])->value('name') ?? 'Staff'];
        }

        return [Role::whereRaw('LOWER(name) = ?', ['admin'])->value('name') ?? 'Admin'];
    }

    private function parseFaceDescriptor(?string $json): ?array
    {
        if ($json === null || trim($json) === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        if (!is_array($decoded) || count($decoded) !== 128) {
            return null;
        }

        $normalized = [];
        foreach ($decoded as $value) {
            if (!is_numeric($value)) {
                return null;
            }
            $normalized[] = (float) $value;
        }

        return $normalized;
    }
}
