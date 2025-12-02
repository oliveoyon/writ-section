@extends('admin.layouts.adminlayout')

@section('title', 'Roles')

@section('content')
<div class="container py-2">

    <!-- Header -->
    <div class="lawyer-card d-flex justify-content-between align-items-center mb-3 p-2" style="background:#00284d; color:#fff;">
        <h4 class="mb-0 profile-section-title text-white">Roles</h4>
        @can('Create Role')
        <button class="btn btn-gold btn-sm px-4" id="addRoleBtn">
            <i class="bi bi-plus-circle"></i> Add Role
        </button>
        @endcan
    </div>

    <!-- Roles Grid -->
    <div class="row g-3" id="rolesContainer">
        @foreach($roles as $role)
        <div class="col-md-3" id="role-{{ $role->id }}">
            <div class="lawyer-card p-3 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <h6 class="role-name">{{ $role->name }}</h6>
                    <div class="role-actions">
                        @can('Edit Role')
                        <button class="btn btn-sm btn-info editBtn" data-id="{{ $role->id }}">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        @endcan
                        @can('Delete Role')
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="{{ $role->id }}">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endcan
                        @can('Assign Permissions')
                        <button class="btn btn-sm btn-success assignBtn" data-id="{{ $role->id }}">
                            <i class="bi bi-key-fill"></i>
                        </button>
                        @endcan
                    </div>
                </div>
                <small class="text-muted">Created: {{ $role->created_at->format('Y-m-d') }}</small>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Add/Edit Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="roleForm">
            @csrf
            <input type="hidden" id="roleId">
            <div class="modal-content lawyer-card p-3">
                <div class="modal-header border-0">
                    <h5 class="profile-section-title" id="roleModalLabel">Add Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-bold">Role Name</label>
                    <input type="text" class="form-control" id="roleName" name="name" required>
                    <div class="invalid-feedback" id="roleNameError"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-gold px-4">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Assign Permissions Modal -->
<div class="modal fade" id="assignPermissionsModal" tabindex="-1" aria-labelledby="assignPermissionsLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form id="assignPermissionsForm">
            @csrf
            <input type="hidden" id="assignRoleId">
            <div class="modal-content lawyer-card p-3">
                <div class="modal-header border-0">
                    <h5 class="profile-section-title" id="assignPermissionsLabel">Assign Permissions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="row g-3" id="permissionsContainer">
                        @foreach($permissions->groupBy('group.name') as $groupName => $groupPermissions)
                        @php $groupId = \Illuminate\Support\Str::slug($groupName ?? 'ungrouped'); @endphp
                        <div class="col-md-4">
                            <div class="permission-group-card p-3 shadow-sm rounded border-start border-success">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>{{ $groupName ?? 'Ungrouped' }}</strong>
                                    <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#group-{{ $groupId }}">
                                        View
                                    </button>
                                </div>
                                <div class="collapse" id="group-{{ $groupId }}">
                                    <div class="card-body p-0 mt-2">
                                        @foreach($groupPermissions as $perm)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input permissionCheckbox" type="checkbox" value="{{ $perm->id }}" data-name="{{ $perm->name }}" id="perm-{{ $perm->id }}">
                                            <label class="form-check-label" for="perm-{{ $perm->id }}">{{ $perm->name }}</label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-gold px-4">Save Permissions</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('css')
<style>
/* Base card style */
.lawyer-card { 
    border-radius: 12px; 
    box-shadow: 0 2px 10px rgba(0,0,0,0.08); 
    margin-bottom: 20px; 
    background: #fff; 
}

/* Buttons */
.role-actions button, .permission-actions button { 
    border-radius: 6px; 
    margin-left: 4px; 
    font-size: 0.85rem; 
}
.btn-gold { 
    background-color: #d4a017; 
    color: #fff; 
    border-radius: 8px; 
    font-weight: 500; 
}
.btn-gold:hover { 
    background-color: #b38b0f; 
}

/* SweetAlert2 */
.swal2-popup .swal2-confirm { 
    background-color: #d4a017 !important; 
}
.swal2-popup .swal2-cancel { 
    background-color: #6b7280 !important; 
}

/* Assign Permissions Modal */
#assignPermissionsModal .modal-content {
    border-radius: 12px;
    background: #fefefe;
}

#assignPermissionsModal .modal-header {
    background-color: #00284d; /* dark blue header */
    color: #fff;
    border-bottom: none;
    padding: 15px 20px;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

#assignPermissionsModal .modal-title {
    font-weight: 600;
    font-size: 1.2rem;
}

#assignPermissionsModal .modal-body {
    max-height: 70vh;
    overflow-y: auto;
    padding: 15px 20px;
}

/* Permission Group Card */
.permission-group-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 15px;
    transition: 0.3s;
    padding: 10px;
}

.permission-group-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(0,0,0,0.12);
}

.permission-group-card strong {
    color: #065f46;
    font-weight: 600;
}

/* Checkbox */
.permissionCheckbox {
    cursor: pointer;
    margin-right: 6px;
}
.permissionCheckbox + label {
    cursor: pointer;
    color: #065f46;
    font-weight: 500;
}

.permissionCheckbox:hover + label {
    color: #d4a017; /* gold hover */
}

/* Collapse Button */
.permission-group-card .btn-collapse {
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 6px;
    padding: 2px 8px;
}

/* Modal Footer */
#assignPermissionsModal .modal-footer {
    border-top: none;
    padding: 15px 20px;
    justify-content: flex-end;
}

/* Scrollbar */
#assignPermissionsModal .modal-body::-webkit-scrollbar {
    width: 6px;
}
#assignPermissionsModal .modal-body::-webkit-scrollbar-thumb {
    background-color: #d4a017;
    border-radius: 10px;
}
</style>

@endpush

@push('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('roleModal'));
    const assignModal = new bootstrap.Modal(document.getElementById('assignPermissionsModal'));
    const form = document.getElementById('roleForm');
    const nameInput = document.getElementById('roleName');
    const roleIdInput = document.getElementById('roleId');
    const roleNameError = document.getElementById('roleNameError');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Open Add Modal
    document.getElementById('addRoleBtn').addEventListener('click', () => {
        form.reset();
        roleIdInput.value = '';
        nameInput.classList.remove('is-invalid');
        roleNameError.textContent = '';
        document.getElementById('roleModalLabel').textContent = 'Add Role';
        modal.show();
    });

    // Delegated click for Edit/Delete/Assign
    document.addEventListener('click', function(e){
        const editBtn = e.target.closest('.editBtn');
        const deleteBtn = e.target.closest('.deleteBtn');
        const assignBtn = e.target.closest('.assignBtn');

        // Edit
        if(editBtn){
            const id = editBtn.dataset.id;
            fetch(`/admin/roles/${id}/edit`)
                .then(res => res.json())
                .then(data => {
                    roleIdInput.value = data.id;
                    nameInput.value = data.name;
                    document.getElementById('roleModalLabel').textContent = 'Edit Role';
                    modal.show();
                });
        }

        // Delete
        if(deleteBtn){
            const id = deleteBtn.dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the role!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then(result => {
                if(result.isConfirmed){
                    const data = new FormData();
                    data.append('_token', csrfToken);
                    data.append('_method', 'DELETE');

                    fetch(`/admin/roles/${id}`, { method:'POST', body:data })
                        .then(res => res.json())
                        .then(data => {
                            if(data.success){
                                document.getElementById(`role-${id}`)?.remove();
                                Swal.fire('Deleted!', data.message, 'success');
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        });
                }
            });
        }

        // Assign Permissions
        if(assignBtn){
            const roleId = assignBtn.dataset.id;
            fetch(`/admin/roles/${roleId}/permissions`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('assignRoleId').value = data.role.id;
                    document.querySelectorAll('.permissionCheckbox').forEach(c => c.checked = false);
                    data.rolePermissions.forEach(pid => {
                        const checkbox = document.querySelector(`.permissionCheckbox[value="${pid}"]`);
                        if(checkbox) checkbox.checked = true;
                    });
                    assignModal.show();
                });
        }
    });

    // Add/Edit Submit
    form.addEventListener('submit', function(e){
        e.preventDefault();
        roleNameError.textContent = '';
        nameInput.classList.remove('is-invalid');

        const id = roleIdInput.value;
        const url = id ? `/admin/roles/${id}` : '/admin/roles';
        const method = id ? 'PUT' : 'POST';

        const data = new FormData();
        data.append('_token', csrfToken);
        if(id) data.append('_method', 'PUT');
        data.append('name', nameInput.value);

        fetch(url, { method:'POST', body:data })
            .then(res => res.json())
            .then(resp => {
                if(resp.success){
                    const cardHtml = `
<div class="col-md-3" id="role-${resp.role.id}">
    <div class="lawyer-card p-3 shadow-sm">
        <div class="d-flex justify-content-between align-items-start">
            <h6 class="role-name">${resp.role.name}</h6>
            <div class="role-actions">
                @can('Edit Role')
                <button class="btn btn-sm btn-info editBtn" data-id="${resp.role.id}">
                    <i class="bi bi-pencil-square"></i>
                </button>
                @endcan
                @can('Delete Role')
                <button class="btn btn-sm btn-danger deleteBtn" data-id="${resp.role.id}">
                    <i class="bi bi-trash"></i>
                </button>
                @endcan
                @can('Assign Permissions')
                <button class="btn btn-sm btn-success assignBtn" data-id="${resp.role.id}">
                    <i class="bi bi-key-fill"></i>
                </button>
                @endcan
            </div>
        </div>
        <small class="text-muted">Created: ${resp.role.created_at.split('T')[0]}</small>
    </div>
</div>`;
                    if(id){
                        document.getElementById(`role-${resp.role.id}`).outerHTML = cardHtml;
                    } else {
                        document.getElementById('rolesContainer').insertAdjacentHTML('beforeend', cardHtml);
                    }
                    modal.hide();
                    Swal.fire('Success', resp.message, 'success');
                } else if(resp.errors && resp.errors.name){
                    roleNameError.textContent = resp.errors.name[0];
                    nameInput.classList.add('is-invalid');
                }
            });
    });

    // Assign Permissions Submit
    document.getElementById('assignPermissionsForm').addEventListener('submit', function(e){
        e.preventDefault();
        const roleId = document.getElementById('assignRoleId').value;
        const permissions = Array.from(document.querySelectorAll('.permissionCheckbox:checked')).map(c => parseInt(c.value));

        const data = new FormData();
        data.append('_token', csrfToken);
        permissions.forEach(p => data.append('permissions[]', p));

        fetch(`/admin/roles/${roleId}/assign-permissions`, { method:'POST', body:data })
            .then(res => res.json())
            .then(resp => {
                if(resp.success){
                    Swal.fire('Success', resp.message, 'success');
                    assignModal.hide();
                }
            });
    });
});


</script>
@endpush
