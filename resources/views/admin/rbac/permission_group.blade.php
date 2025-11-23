@extends('dashboard.layouts.admin')
@section('title', 'Permission Groups')

@section('content')
<div class="app-content-header py-3">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 page-title">Permission Groups</h3>
            </div>
            <div class="col-sm-6 text-end">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Permission Groups</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content py-3">
    <div class="container-fluid">
        <div class="mb-4 d-flex justify-content-end">
            @can('Create Permission Group')
            <button class="btn btn-success btn-modern" id="addGroupBtn">
                <i class="bi bi-plus-circle"></i> Add Group
            </button>
            @endcan
        </div>

        <div class="row g-3" id="groupsContainer">
            @foreach($groups as $group)
            <div class="col-md-3" id="group-{{ $group->id }}">
                <div class="group-card shadow-sm rounded">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="group-name">{{ $group->name }}</h5>
                        <div class="group-actions">
                            @can('Edit Permission Group')
                            <button class="btn btn-sm btn-info editBtn" data-id="{{ $group->id }}">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            @endcan
                            @can('Delete Permission Group')
                            <button class="btn btn-sm btn-danger deleteBtn" data-id="{{ $group->id }}">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endcan
                        </div>
                    </div>
                    <small class="text-bold">Total Permissions: {{ $group->permissions_count }}</small>
                    
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="groupModal" tabindex="-1" aria-labelledby="groupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="groupForm" class="modal-content">
            @csrf
            <input type="hidden" id="groupId">
            <div class="modal-header">
                <h5 class="modal-title" id="groupModalLabel">Add Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="groupName" class="form-label">Group Name</label>
                    <input type="text" class="form-control" id="groupName" name="name" required>
                    <div class="invalid-feedback" id="nameError"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="saveGroupBtn">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
body {
    background-color: #f9faf7;
    font-family: 'Source Sans 3', sans-serif;
    color: #374151;
}

/* Page header */
.app-content-header {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    padding: 20px;
    margin-bottom: 25px;
}

.page-title {
    font-weight: 600;
    color: #047857; /* soft green */
}

/* Breadcrumb */
.breadcrumb {
    background: transparent;
    justify-content: flex-end !important;
}

.breadcrumb a {
    color: #4b5563;
    text-decoration: none;
    transition: color 0.2s ease;
}

.breadcrumb a:hover { color: #065f46; text-decoration: underline; }

/* Card layout */
.group-card {
    background-color: #e6f4ea;
    padding: 18px;
    border-radius: 14px;
    transition: all 0.3s ease;
    position: relative;
}

.group-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.group-name {
    font-weight: 600;
    color: #047857;
}

.group-actions button {
    border-radius: 6px;
    margin-left: 5px;
}

.group-actions button:hover {
    opacity: 0.85;
}

/* Buttons */
.btn-modern {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-success.btn-modern:hover { background-color: #059669; }
.btn-info.btn-modern:hover { background-color: #0d9488; }
.btn-danger.btn-modern:hover { background-color: #dc2626; }

/* Modal */
.modal-content {
    border-radius: 14px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.1);
}

.modal-header { border-bottom: none; font-weight: 600; color: #047857; }
.modal-footer { border-top: none; }

.form-control:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 0.2rem rgba(16,185,129,0.25);
}

/* SweetAlert buttons */
.swal2-popup .swal2-confirm { background-color: #10b981 !important; }
.swal2-popup .swal2-cancel { background-color: #6b7280 !important; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('groupModal');
    const modal = new bootstrap.Modal(modalEl);
    const addBtn = document.getElementById('addGroupBtn');
    const form = document.getElementById('groupForm');
    const nameInput = document.getElementById('groupName');
    const groupIdInput = document.getElementById('groupId');
    const nameError = document.getElementById('nameError');
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const container = document.getElementById('groupsContainer');

    addBtn?.addEventListener('click', () => {
        groupIdInput.value = '';
        form.reset();
        nameInput.classList.remove('is-invalid');
        nameError.textContent = '';
        document.getElementById('groupModalLabel').textContent = 'Add Group';
        modal.show();
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.editBtn')) {
            const btn = e.target.closest('.editBtn');
            const id = btn.dataset.id;
            fetch(`/admin/permission-groups/${id}/edit`)
                .then(res => res.json())
                .then(data => {
                    groupIdInput.value = data.id;
                    nameInput.value = data.name;
                    document.getElementById('groupModalLabel').textContent = 'Edit Group';
                    nameInput.classList.remove('is-invalid');
                    nameError.textContent = '';
                    modal.show();
                });
        }
        if (e.target.closest('.deleteBtn')) {
            const btn = e.target.closest('.deleteBtn');
            const id = btn.dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the group!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/permission-groups/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': token }
                    })
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById(`group-${id}`)?.remove();
                        Swal.fire('Deleted!', data.message, 'success');
                    });
                }
            });
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        nameError.textContent = '';
        nameInput.classList.remove('is-invalid');

        const id = groupIdInput.value;
        const url = id ? `/admin/permission-groups/${id}` : '/admin/permission-groups';
        const method = id ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ name: nameInput.value })
        })
        .then(async res => {
            if (res.status === 422) {
                const data = await res.json();
                nameError.textContent = data.errors.name ? data.errors.name[0] : '';
                nameInput.classList.add('is-invalid');
            } else {
                return res.json();
            }
        })
        .then(data => {
            if (data) {
                const cardHtml = `
                <div class="col-md-4" id="group-${data.group.id}">
                    <div class="group-card shadow-sm rounded">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="group-name">${data.group.name}</h5>
                            <div class="group-actions">
                                <button class="btn btn-sm btn-info editBtn" data-id="${data.group.id}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.group.id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Created: ${data.group.created_at.split('T')[0]}</small>
                    </div>
                </div>
                `;
                if (id) {
                    document.getElementById(`group-${id}`).outerHTML = cardHtml;
                } else {
                    container.insertAdjacentHTML('beforeend', cardHtml);
                }
                modal.hide();
                Swal.fire('Success', data.message, 'success');
            }
        });
    });
});
</script>
@endpush
@endsection
