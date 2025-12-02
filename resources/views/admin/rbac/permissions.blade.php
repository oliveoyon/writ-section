@extends('admin.layouts.adminlayout')

@section('title', 'Permissions')

@section('content')
<div class="container py-2">

    <!-- Header -->
    <div class="lawyer-card d-flex justify-content-between align-items-center mb-3 p-2" style="background:#00284d; color:#fff;">
        <h4 class="mb-0 profile-section-title text-white">Permissions</h4>
        @can('Create Permission')
        <button class="btn btn-gold btn-sm px-4" data-bs-toggle="modal" data-bs-target="#permissionModal">
            <i class="bi bi-plus-circle"></i> Add Permission
        </button>
        @endcan
    </div>

    <!-- Accordion for Permission Groups -->
    <div class="lawyer-card p-3">
        <div class="accordion" id="permissionsAccordion">
            @foreach ($groups as $group)
            <div class="accordion-item mb-3">
                <h2 class="accordion-header" id="heading{{ $group->id }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse{{ $group->id }}" aria-expanded="false"
                        aria-controls="collapse{{ $group->id }}">
                        {{ $group->name }} ({{ $group->permissions_count }} Permissions)
                    </button>
                </h2>
                <div id="collapse{{ $group->id }}" class="accordion-collapse collapse"
                    aria-labelledby="heading{{ $group->id }}" data-bs-parent="#permissionsAccordion">
                    <div class="accordion-body">
                        <div class="row g-3">
                            @foreach ($permissions->where('group_id', $group->id) as $permission)
                            <div class="col-md-3" id="permission-{{ $permission->id }}">
                                <div class="permission-card shadow-sm rounded">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="permission-name">{{ $permission->name }}</h6>
                                        <div class="permission-actions">
                                            @can('Edit Permission')
                                            <button class="btn btn-sm btn-info editBtn" data-id="{{ $permission->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            @endcan
                                            @can('Delete Permission')
                                            <button class="btn btn-sm btn-danger deleteBtn" data-id="{{ $permission->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </div>
                                    <small class="text-muted">Created: {{ $permission->created_at->format('Y-m-d') }}</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Permission Modal -->
<div class="modal fade" id="permissionModal" tabindex="-1" aria-labelledby="permissionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="permissionForm">
            @csrf
            <input type="hidden" id="permissionId">
            <div class="modal-content lawyer-card p-3">
                <div class="modal-header border-0">
                    <h5 class="profile-section-title" id="permissionModalLabel">Add Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-bold">Permission Name</label>
                    <input type="text" class="form-control" id="permissionName" name="name" required>
                    <div class="invalid-feedback" id="nameError"></div>

                    <label class="form-label fw-bold mt-3">Group</label>
                    <select class="form-control" id="groupSelect" name="group_id" required>
                        <option value="">-- Select Group --</option>
                        @foreach ($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback" id="groupError"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-gold px-4">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('css')
<style>
.lawyer-card { border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.08); margin-bottom:20px; }
.permission-card { background:#fff; padding:15px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.08); transition:0.3s; }
.permission-card:hover { transform:translateY(-2px); box-shadow:0 8px 18px rgba(0,0,0,0.12); }
.permission-name { font-weight:600; color:#065f46; }
.permission-actions button { border-radius:6px; margin-left:4px; font-size:0.85rem; }
.btn-gold { background-color:#d4a017; color:#fff; border-radius:8px; font-weight:500; }
.btn-gold:hover { background-color:#b38b0f; }
.swal2-popup .swal2-confirm { background-color:#d4a017 !important; }
.swal2-popup .swal2-cancel { background-color:#6b7280 !important; }
</style>
@endpush

@push('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('permissionModal'));
    const form = document.getElementById('permissionForm');
    const nameInput = document.getElementById('permissionName');
    const groupSelect = document.getElementById('groupSelect');
    const permissionIdInput = document.getElementById('permissionId');
    const nameError = document.getElementById('nameError');
    const groupError = document.getElementById('groupError');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Open Add Modal
    document.querySelector('[data-bs-target="#permissionModal"]')?.addEventListener('click', () => {
        form.reset();
        permissionIdInput.value = '';
        nameInput.classList.remove('is-invalid');
        groupSelect.classList.remove('is-invalid');
        nameError.textContent = '';
        groupError.textContent = '';
        document.getElementById('permissionModalLabel').textContent = 'Add Permission';
    });

    // Delegated click for Edit/Delete
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.editBtn');
        const deleteBtn = e.target.closest('.deleteBtn');

        // Edit
        if(editBtn){
            const id = editBtn.dataset.id;
            fetch(`/admin/permissions/${id}/edit`)
                .then(res => res.json())
                .then(data => {
                    permissionIdInput.value = data.id;
                    nameInput.value = data.name;
                    groupSelect.value = data.group_id;
                    document.getElementById('permissionModalLabel').textContent = 'Edit Permission';
                    modal.show();
                });
        }

        // Delete
        if(deleteBtn){
            const id = deleteBtn.dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the permission!",
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

                    fetch(`/admin/permissions/${id}`, {
                        method: 'POST',
                        body: data
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success){
                            document.getElementById(`permission-${id}`)?.remove();
                            Swal.fire('Deleted!', data.message, 'success');
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
                }
            });
        }
    });

    // Add/Edit Submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        nameError.textContent = '';
        groupError.textContent = '';
        nameInput.classList.remove('is-invalid');
        groupSelect.classList.remove('is-invalid');

        const id = permissionIdInput.value;
        const url = id ? `/admin/permissions/${id}` : '/admin/permissions';
        const method = id ? 'PUT' : 'POST';

        const data = new FormData();
        data.append('_token', csrfToken);
        if(id) data.append('_method', 'PUT');
        data.append('name', nameInput.value);
        data.append('group_id', groupSelect.value);

        fetch(url, { method: 'POST', body: data })
            .then(res => res.json())
            .then(resp => {
                if(resp.success){
                    const cardHtml = `
<div class="col-md-3" id="permission-${resp.permission.id}">
    <div class="permission-card shadow-sm rounded">
        <div class="d-flex justify-content-between align-items-start">
            <h6 class="permission-name">${resp.permission.name}</h6>
            <div class="permission-actions">
                <button class="btn btn-sm btn-info editBtn" data-id="${resp.permission.id}">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-sm btn-danger deleteBtn" data-id="${resp.permission.id}">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
        <small class="text-muted">Created: ${resp.permission.created_at.split('T')[0]}</small>
    </div>
</div>`;
                    const container = document.querySelector(`#collapse${resp.permission.group_id} .accordion-body .row`);
                    if(id){
                        document.getElementById(`permission-${resp.permission.id}`).outerHTML = cardHtml;
                    } else {
                        container.insertAdjacentHTML('beforeend', cardHtml);
                    }
                    modal.hide();
                    Swal.fire('Success', resp.message, 'success');
                } else if(resp.errors){
                    if(resp.errors.name){ nameError.textContent = resp.errors.name[0]; nameInput.classList.add('is-invalid'); }
                    if(resp.errors.group_id){ groupError.textContent = resp.errors.group_id[0]; groupSelect.classList.add('is-invalid'); }
                }
            });
    });
});

</script>
@endpush
