@extends('admin.layouts.adminlayout')
@section('title', 'Users')

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded" style="background:#00284d;color:#fff;">
        <h4 class="mb-0">User Management</h4>
        <a href="{{ route('admin.users.create') }}" class="btn btn-warning btn-sm fw-bold">Create User</a>
    </div>

    @if(session('swal-success'))
        <div class="alert alert-success">{{ session('swal-success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Login ID</th>
                            <th>Department</th>
                            <th>User Type</th>
                            <th>Status</th>
                            <th width="160">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->login_id ?? '-' }}</td>
                                <td>{{ $user->departmentRelation?->name ?? '-' }}</td>
                                <td><span class="badge bg-info text-dark">{{ ucfirst($user->user_type) }}</span></td>
                                <td>
                                    <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

