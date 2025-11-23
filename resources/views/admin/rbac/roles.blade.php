@extends('dashboard.layouts.admin')
@section('title', 'Roles')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Roles</h3>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-end">
                    @can('Create Role')
                    <button class="btn btn-primary" id="addRoleBtn">
                        <i class="fas fa-plus"></i> Add Role
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row g-4" id="roleCards">
            @foreach($roles as $role)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card role-card shadow-sm p-3 mb-3" style="background-color:#e6f4ea; border-radius:12px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">{{ $role->name }}</h5>
                            @if($role->name=='Super Admin')
                            <span class="badge bg-danger">Super</span>
                            @endif
                        </div>
                        <p class="mb-1"><strong>Permissions:</strong> <span class="badge bg-info">{{ $role->permissions->count() }}</span></p>
                        <div class="d-flex flex-wrap gap-2">
                            @can('Edit Role')
                            <button class="btn btn-sm btn-outline-primary editBtn" data-id="{{ $role->id }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            @endcan
                            @can('Delete Role')
                            <button class="btn btn-sm btn-outline-danger deleteBtn" data-id="{{ $role->id }}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            @endcan
                            @can('Assign Permissions')
                            <button class="btn btn-sm btn-outline-success assignBtn" data-id="{{ $role->id }}">
                                <i class="fas fa-key"></i> Permissions
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Add/Edit Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="roleForm">
                @csrf
                <input type="hidden" id="roleId">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add Role</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="roleName" class="form-control" placeholder="Role Name" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Permissions Modal -->
<div class="modal fade" id="assignPermissionsModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <form id="assignPermissionsForm">
                @csrf
                <input type="hidden" id="assignRoleId">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Assign Permissions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="overflow-y:auto; max-height: calc(100vh - 150px);">
                    <div class="row g-3">
                        @foreach($permissions->groupBy('group.name') as $groupName => $groupPermissions)
                        @php $groupId = \Illuminate\Support\Str::slug($groupName ?? 'ungrouped'); @endphp
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="card shadow-sm" style="border-left:4px solid #28a745;">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <strong>{{ $groupName ?? 'Ungrouped' }}</strong>
                                    <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#group-{{ $groupId }}" aria-expanded="false">
                                        View
                                    </button>
                                </div>
                                <div class="collapse" id="group-{{ $groupId }}">
                                    <div class="card-body" style="background:#fff;">
                                        @foreach($groupPermissions as $perm)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input permissionCheckbox group-{{ $groupId }}" type="checkbox" value="{{ $perm->id }}" id="perm-{{ $perm->id }}">
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
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Permissions</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const roleModal = new bootstrap.Modal(document.getElementById('roleModal'));
const assignModal = new bootstrap.Modal(document.getElementById('assignPermissionsModal'));

// Add/Edit Role
document.getElementById('addRoleBtn').addEventListener('click', () => {
    document.getElementById('roleForm').reset();
    document.getElementById('roleId').value = '';
    roleModal.show();
});
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        fetch(`/admin/roles/${id}/edit`).then(r => r.json()).then(d => {
            document.getElementById('roleId').value = d.id;
            document.getElementById('roleName').value = d.name;
            roleModal.show();
        });
    });
});
document.getElementById('roleForm').addEventListener('submit', e => {
    e.preventDefault();
    const id = document.getElementById('roleId').value;
    const url = id ? `/admin/roles/${id}` : '/admin/roles';
    const method = id ? 'PUT' : 'POST';
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ name: document.getElementById('roleName').value })
    }).then(r => r.json()).then(() => location.reload());
});

// Delete Role
document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        if (confirm('Delete this role?')) {
            fetch(`/admin/roles/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': token } })
            .then(() => location.reload());
        }
    });
});

// Assign Permissions
document.querySelectorAll('.assignBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const roleId = btn.dataset.id;
        fetch(`/admin/roles/${roleId}/permissions`).then(r => r.json()).then(d => {
            document.getElementById('assignRoleId').value = d.role.id;
            document.querySelectorAll('.permissionCheckbox').forEach(c => c.checked = false);
            d.rolePermissions.forEach(pid => {
                const checkbox = document.querySelector(`.permissionCheckbox[value="${pid}"]`);
                if (checkbox) { checkbox.checked = true; checkbox.dispatchEvent(new Event('change')); }
            });
            assignModal.show();
        });
    });
});

// Update count
document.addEventListener('change', e => {
    if (e.target.classList.contains('permissionCheckbox')) {
        const classes = e.target.className.split(/\s+/);
        const groupClass = classes.find(c => c.startsWith('group-'));
        if (groupClass) {
            const checkedCount = document.querySelectorAll('.' + groupClass + ':checked').length;
            // optional: show count somewhere if needed
        }
    }
});

// Save Permissions
document.getElementById('assignPermissionsForm').addEventListener('submit', e => {
    e.preventDefault();
    const roleId = document.getElementById('assignRoleId').value;
    const permissions = Array.from(document.querySelectorAll('.permissionCheckbox:checked')).map(c => parseInt(c.value));
    fetch(`/admin/roles/${roleId}/assign-permissions`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json','X-CSRF-TOKEN': token },
        body: JSON.stringify({ permissions })
    }).then(r => r.json()).then(d => {
        if(d.success){ alert(d.message); assignModal.hide(); }
    });
});
</script>
@endpush
