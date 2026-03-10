@extends('admin.layouts.adminlayout')
@section('title', isset($user) ? 'Edit User' : 'Create User')

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded" style="background:#00284d;color:#fff;">
        <h4 class="mb-0">{{ isset($user) ? 'Edit User' : 'Create User' }}</h4>
        <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}" method="POST">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Login ID (Card)</label>
                        <input type="text" name="login_id" class="form-control" value="{{ old('login_id', $user->login_id ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-select" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department', $user->department ?? '') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">User Type</label>
                        <select name="user_type" class="form-select" required>
                            <option value="admin" {{ old('user_type', $user->user_type ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="staff" {{ old('user_type', $user->user_type ?? '') === 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                {{ old('is_active', isset($user) ? (bool) $user->is_active : true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active User</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Password {{ isset($user) ? '(Keep blank to keep current)' : '' }}</label>
                        <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" {{ isset($user) ? '' : 'required' }}>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-warning fw-bold">{{ isset($user) ? 'Update User' : 'Create User' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

