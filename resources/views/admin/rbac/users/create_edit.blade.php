@extends('admin.layouts.adminlayout')
@section('title', isset($user) ? 'Edit User' : 'Create User')

@section('content')
@php($isEdit = isset($user))
<div class="container py-4 user-form-page">
    <div class="user-form-header mb-3">
        <div>
            <div class="system-mark">RTFTS Users</div>
            <h4 class="mb-0">{{ $isEdit ? 'Edit User' : 'Create User' }}</h4>
            <small>{{ $isEdit ? ($user->name ?? '') : 'Staff and admin account setup' }}</small>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-brand btn-sm">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ $isEdit ? route('admin.users.update', $user->id) : route('admin.users.store') }}" method="POST" class="admin-panel user-form-panel">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="panel-heading">
            <div>
                <h5>Account Information</h5>
                <span>{{ $isEdit ? 'Update user access details' : 'Create a new system user' }}</span>
            </div>
            <div class="active-switch">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                    {{ old('is_active', $isEdit ? (bool) $user->is_active : true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>

        <div class="panel-body">
            <div class="form-section">
                <div class="section-title">
                    <i class="bi bi-person-badge" aria-hidden="true"></i>
                    <span>Identity</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" autofocus required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="employee_id">Employee ID</label>
                        <input type="text" id="employee_id" name="employee_id" class="form-control" value="{{ old('employee_id', $user->employee_id ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="login_id">Card ID</label>
                        <input type="text" id="login_id" name="login_id" class="form-control" value="{{ old('login_id', $user->login_id ?? '') }}">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <span>Access</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="department">Department</label>
                        <select name="department" id="department" class="form-select" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" data-system-name="{{ $dept->name }}" {{ old('department', $user->department ?? '') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="user_type">User Type</label>
                        <select name="user_type" id="user_type" class="form-select" required>
                            <option value="staff" {{ old('user_type', $user->user_type ?? 'staff') === 'staff' ? 'selected' : '' }}>{{ $userTypeLabels['staff'] }}</option>
                            <option value="admin" {{ old('user_type', $user->user_type ?? 'staff') === 'admin' ? 'selected' : '' }}>{{ $userTypeLabels['admin'] }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">
                    <i class="bi bi-key" aria-hidden="true"></i>
                    <span>Password</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="password">Password {{ $isEdit ? '(optional)' : '' }}</label>
                        <input type="password" id="password" name="password" class="form-control" {{ $isEdit ? '' : 'required' }}>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" {{ $isEdit ? '' : 'required' }}>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-brand">
                <i class="bi bi-check2-circle" aria-hidden="true"></i>
                {{ $isEdit ? 'Update User' : 'Create User' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('css')
<style>
    .user-form-page { max-width: 960px; }
    .user-form-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        background: #fff;
        border: 1px solid #e3e8ef;
        border-top: 3px solid #00284d;
        border-bottom-color: #d4a017;
        border-radius: 4px;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .08);
    }
    .user-form-header h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
    .user-form-header small { color: #6b7280; font-weight: 600; }
    .system-mark { color: #b87d08; font-size: .82rem; font-weight: 800; }
    .admin-panel {
        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: 4px;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .07);
        overflow: hidden;
    }
    .panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .8rem 1rem;
        background: #fbfcfe;
        border-top: 3px solid #00284d;
        border-bottom: 1px solid #e5e7eb;
    }
    .panel-heading h5 { margin: 0; color: #1f2937; font-size: 1rem; font-weight: 800; }
    .panel-heading span { color: #6b7280; font-size: .84rem; font-weight: 600; }
    .panel-body { padding: 1rem; }
    .form-section + .form-section {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #edf0f2;
    }
    .section-title {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        margin-bottom: .75rem;
        color: #00284d;
        font-size: .9rem;
        font-weight: 900;
    }
    .section-title i { color: #d4a017; }
    .form-label { color: #374151; font-size: .84rem; font-weight: 800; margin-bottom: .35rem; }
    .form-control,
    .form-select {
        min-height: 42px;
        border-radius: 4px;
        border-color: #d7dde5;
    }
    .form-control:focus,
    .form-select:focus {
        border-color: #d4a017;
        box-shadow: 0 0 0 .15rem rgba(212, 160, 23, .15);
    }
    .active-switch {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        padding: .45rem .65rem;
        border: 1px solid #d8e3ef;
        border-radius: 4px;
        background: #f7fbff;
        color: #00284d;
        font-weight: 800;
        white-space: nowrap;
    }
    .active-switch .form-check-input {
        margin: 0;
        cursor: pointer;
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: .5rem;
        padding: 1rem;
        background: #fbfcfe;
        border-top: 1px solid #e5e7eb;
    }
    .btn-brand {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: #00284d;
        color: #fff;
        border-color: #00284d;
        font-weight: 800;
    }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-outline-brand {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        color: #00284d;
        border-color: #00284d;
        border-radius: 4px;
        font-weight: 800;
    }
    .btn-outline-brand:hover { color: #fff; background: #00284d; border-color: #00284d; }
    @media (max-width: 575.98px) {
        .user-form-header,
        .panel-heading,
        .form-actions {
            align-items: stretch;
            flex-direction: column;
        }
        .user-form-header .btn,
        .form-actions .btn {
            width: 100%;
            justify-content: center;
        }
        .active-switch { justify-content: space-between; }
    }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const department = document.getElementById('department');
    const userType = document.getElementById('user_type');
    const adminOption = userType.querySelector('option[value="admin"]');

    function applyDepartmentAccess() {
        const selected = department.options[department.selectedIndex];
        const adminAllowed = selected?.dataset.systemName === 'Assistant Registrar Office';

        adminOption.disabled = !adminAllowed;
        if (!adminAllowed) {
            userType.value = 'staff';
        }
    }

    department.addEventListener('change', applyDepartmentAccess);
    applyDepartmentAccess();
});
</script>
@endpush
