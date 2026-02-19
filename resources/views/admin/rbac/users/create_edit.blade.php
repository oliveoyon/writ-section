@extends('admin.layouts.adminlayout')
@section('title', isset($user) ? 'Edit User' : 'Create User')

@section('content')
<div class="container py-2">

    <!-- Header -->
    <div class="lawyer-card d-flex justify-content-between align-items-center mb-3 p-2"
         style="background:#00284d; color:#fff;">
        <h4 class="mb-0 profile-section-title text-white">
            {{ isset($user) ? 'Edit User' : 'Create User' }}
        </h4>
    </div>

    <div class="lawyer-card p-3">

        <form id="user-form"
              action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}"
              method="POST">
            @csrf
            @if(isset($user))
                @method('PUT')
            @endif

            <!-- Basic Info -->
            <div class="card mb-4 shadow-sm border-start border-success border-4">
                <div class="card-header bg-light fw-semibold">Basic Information</div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $user->name ?? '') }}" required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Login ID <small>(Card Number for staff only)</small></label>
                            <input type="text" name="login_id" class="form-control"
                                   value="{{ old('login_id', $user->login_id ?? '') }}" required>
                            @error('login_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $user->email ?? '') }}" required>
                            @error('email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone_number" class="form-control"
                                   value="{{ old('phone_number', $user->phone_number ?? '') }}">
                            @error('phone_number')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select name="department" class="form-select" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ old('department', $user->department ?? '') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                    {{ isset($user) && $user->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
                            @error('password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" {{ isset($user) ? '' : 'required' }}>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">User Type</label>
                            <select name="user_type" class="form-select" required>
                                <option value="">Select User Type</option>
                                <option value="admin" {{ (old('user_type', $user->user_type ?? '') == 'admin') ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('user_type')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            <!-- Roles -->
            <div class="card mb-4 shadow-sm border-start border-success border-4">
                <div class="card-header bg-light fw-semibold">Assign Roles</div>
                <div class="card-body d-flex flex-wrap gap-2">
                    @foreach($roles as $role)
                        <input type="checkbox" class="btn-check" id="role-{{ $role->id }}" name="roles[]"
                               value="{{ $role->name }}" autocomplete="off"
                               {{ isset($userRoles) && in_array($role->name, $userRoles) ? 'checked' : '' }}>
                        <label class="btn btn-outline-success" for="role-{{ $role->id }}">{{ $role->name }}</label>
                    @endforeach
                </div>
            </div>

            <!-- Permissions (Collapsible) -->
            <div class="card mb-4 shadow-sm border-start border-success border-4">
                <div class="card-header bg-light fw-semibold d-flex justify-content-between align-items-center">
                    <span>Assign Permissions</span>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#permissionsSection" aria-expanded="false" aria-controls="permissionsSection">
                        Toggle
                    </button>
                </div>
                <div id="permissionsSection" class="collapse">
                    <div class="card-body">
                        @foreach($permissionGroups as $group)
                            <div class="card mb-2" style="background-color: #f0f9f0;">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>{{ $group->name }}</strong>
                                    <div>
                                        <input type="checkbox" class="select-all" data-group="{{ $group->id }}">
                                        Select All
                                        <span class="badge bg-secondary" id="count-{{ $group->id }}">0</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @foreach($group->permissions as $permission)
                                        @php
                                            $isDirect = in_array($permission->name, $directPermissions ?? []);
                                            $isViaRole = isset($user) && $user->hasPermissionTo($permission->name) && !$isDirect;
                                            $isChecked = $isDirect || $isViaRole;
                                            $isDisabled = $isViaRole ? 'disabled' : '';
                                        @endphp
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input permission-checkbox group-{{ $group->id }}"
                                                   type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                                   {{ $isChecked ? 'checked' : '' }} {{ $isDisabled }}>
                                            <label class="form-check-label">
                                                {{ $permission->name }}
                                                @if ($isViaRole && !$isDirect)
                                                    <small class="text-muted">(via role)</small>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <button type="submit" id="submit-btn" class="btn btn-gold">
                {{ isset($user) ? 'Update' : 'Create' }}
            </button>

        </form>

    </div>
</div>
@endsection

@push('css')
<style>
.btn-gold {
    background-color: #d4a017;
    color: #fff;
    border-radius: 8px;
    font-weight: 500;
}
.btn-gold:hover {
    background-color: #b38b0f;
}
.card-header button {
    font-size: 0.85rem;
}
.form-check.form-switch {
    padding-left: 0;
}
.text-danger {
    font-size: 0.85rem;
}
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {

    function updateCount(groupId) {
        const count = Array.from(document.querySelectorAll('.group-' + groupId + ':checked'))
            .filter(cb => !cb.disabled).length;
        document.getElementById('count-' + groupId).textContent = count;
    }

    document.querySelectorAll('.select-all').forEach(toggle => {
        const groupId = toggle.dataset.group;
        updateCount(groupId);
        toggle.addEventListener('change', function() {
            const checked = this.checked;
            document.querySelectorAll('.group-' + groupId).forEach(cb => {
                if (!cb.disabled) cb.checked = checked;
            });
            updateCount(groupId);
        });
    });

    document.querySelectorAll('.permission-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const groupClass = Array.from(this.classList).find(c => c.startsWith('group-'));
            if (groupClass) {
                const groupId = groupClass.split('-')[1];
                updateCount(groupId);
            }
        });
    });

    // Form submit with SweetAlert
    const form = document.getElementById('user-form');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;

        const formData = new FormData(form);

        fetch(form.action, {
            method: form.method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        }).then(async res => {
            submitBtn.disabled = false;
            if (res.ok) {
                return res.json();
            } else if (res.status === 422) {
                const errorData = await res.json();
                let messages = '';
                Object.values(errorData.errors).forEach(arr => {
                    messages += arr.join('<br>') + '<br>';
                });
                throw new Error(messages);
            } else {
                throw new Error('Something went wrong!');
            }
        }).then(res => {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                html: res.success ?? res.message ?? 'Saved successfully',
                confirmButtonText: 'OK'
            }).then(() => window.location.href = "{{ route('admin.users.index') }}");
        }).catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: err.message
            });
            console.error(err);
        });
    });

});
</script>
@endpush
