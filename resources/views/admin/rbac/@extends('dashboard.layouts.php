@extends('dashboard.layouts.admin')
@section('title', 'Permissions')

@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 page-title">Permissions</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Permissions</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content py-3">
        <div class="container-fluid">
            <div class="mb-4 d-flex justify-content-end">
                @can('Create Permission')
                    <button class="btn btn-success btn-modern" id="addPermissionBtn">
                        <i class="bi bi-plus-circle"></i> Add Permission
                    </button>
                @endcan
            </div>

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
                                                            <button class="btn btn-sm btn-info editBtn"
                                                                data-id="{{ $permission->id }}">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </button>
                                                        @endcan
                                                        @can('Delete Permission')
                                                            <button class="btn btn-sm btn-danger deleteBtn"
                                                                data-id="{{ $permission->id }}">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        @endcan
                                                    </div>
                                                </div>
                                                <small class="text-muted">Created:
                                                    {{ $permission->created_at->format('Y-m-d') }}</small>
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
            <div class="modal-content">
                <form id="permissionForm">
                    @csrf
                    <input type="hidden" id="permissionId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="permissionModalLabel">Add Permission</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="permissionName" class="form-label">Permission Name</label>
                            <input type="text" class="form-control" id="permissionName" name="name" required>
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="groupSelect" class="form-label">Group</label>
                            <select class="form-control" id="groupSelect" name="group_id" required>
                                <option value="">-- Select Group --</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="groupError"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="savePermissionBtn">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Accordion Header */
            .accordion-button {
                border-radius: 10px;
                background-color: #d1f7e0;
                /* soft green */
                color: #065f46;
                font-weight: 600;
                border-left: 4px solid transparent;
                transition: all 0.3s ease;
            }

            .accordion-button.collapsed {
                background-color: #e6f4ea;
                /* light green */
                color: #047857;
                border-left: 4px solid #10b981;
            }

            .accordion-button:not(.collapsed) {
                background-color: #10b981;
                /* medium green */
                color: #ffffff;
                border-left: 4px solid #047857;
            }

            /* Cards */
            .permission-card {
                background-color: #ffffff;
                padding: 15px;
                border-radius: 12px;
                transition: all 0.3s ease;
                position: relative;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
                /* subtle shadow for separation */
            }

            .permission-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 18px rgba(0, 0, 0, 0.12);
            }

            /* Card Header Elements */
            .permission-name {
                font-weight: 600;
                color: #065f46;
            }

            /* Action Buttons */
            .permission-actions button {
                border-radius: 6px;
                margin-left: 4px;
                font-size: 0.85rem;
            }

            /* Modern Buttons */
            .btn-modern {
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.2s ease;
            }

            .btn-success.btn-modern:hover {
                background-color: #059669;
            }

            .btn-info.btn-modern:hover {
                background-color: #0d9488;
            }

            .btn-danger.btn-modern:hover {
                background-color: #dc2626;
            }

            /* Modal */
            .modal-content {
                border-radius: 14px;
                box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
            }

            .modal-header {
                border-bottom: none;
                font-weight: 600;
                color: #047857;
            }

            .modal-footer {
                border-top: none;
            }

            /* Form focus */
            .form-control:focus {
                border-color: #10b981;
                box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25);
            }

            /* SweetAlert buttons */
            .swal2-popup .swal2-confirm {
                background-color: #10b981 !important;
            }

            .swal2-popup .swal2-cancel {
                background-color: #6b7280 !important;
            }
        </style>
    @endpush


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = new bootstrap.Modal(document.getElementById('permissionModal'));
                const addBtn = document.getElementById('addPermissionBtn');
                const form = document.getElementById('permissionForm');
                const nameInput = document.getElementById('permissionName');
                const groupSelect = document.getElementById('groupSelect');
                const permissionIdInput = document.getElementById('permissionId');
                const nameError = document.getElementById('nameError');
                const groupError = document.getElementById('groupError');
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                addBtn?.addEventListener('click', () => {
                    form.reset();
                    permissionIdInput.value = '';
                    nameInput.classList.remove('is-invalid');
                    groupSelect.classList.remove('is-invalid');
                    nameError.textContent = '';
                    groupError.textContent = '';
                    document.getElementById('permissionModalLabel').textContent = 'Add Permission';
                    modal.show();
                });

                document.addEventListener('click', function(e) {
                    if (e.target.closest('.editBtn')) {
                        const id = e.target.closest('.editBtn').dataset.id;
                        fetch(`/admin/permissions/${id}/edit`).then(res => res.json()).then(data => {
                            permissionIdInput.value = data.id;
                            nameInput.value = data.name;
                            groupSelect.value = data.group_id || '';
                            document.getElementById('permissionModalLabel').textContent =
                                'Edit Permission';
                            nameInput.classList.remove('is-invalid');
                            groupSelect.classList.remove('is-invalid');
                            nameError.textContent = '';
                            groupError.textContent = '';
                            modal.show();
                        });
                    }
                    if (e.target.closest('.deleteBtn')) {
                        const id = e.target.closest('.deleteBtn').dataset.id;
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This will delete the permission!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete it!'
                        }).then(result => {
                            if (result.isConfirmed) {
                                fetch(`/admin/permissions/${id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': token
                                    }
                                }).then(res => res.json()).then(data => {
                                    document.getElementById(`permission-${id}`)?.remove();
                                    Swal.fire('Deleted!', data.message, 'success');
                                });
                            }
                        });
                    }
                });

                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    nameError.textContent = '';
                    groupError.textContent = '';
                    nameInput.classList.remove('is-invalid');
                    groupSelect.classList.remove('is-invalid');

                    const id = permissionIdInput.value;
                    const url = id ? `/admin/permissions/${id}` : '/admin/permissions';
                    const method = id ? 'PUT' : 'POST';

                    fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                name: nameInput.value,
                                group_id: groupSelect.value
                            })
                        })
                        .then(async res => {
                            if (res.status === 422) {
                                const data = await res.json();
                                if (data.errors.name) {
                                    nameError.textContent = data.errors.name[0];
                                    nameInput.classList.add('is-invalid');
                                }
                                if (data.errors.group_id) {
                                    groupError.textContent = data.errors.group_id[0];
                                    groupSelect.classList.add('is-invalid');
                                }
                            } else return res.json();
                        })
                        .then(data => {
                            if (data) {
                                const cardHtml = `
                <div class="col-md-3" id="permission-${data.permission.id}">
                    <div class="permission-card shadow-sm rounded">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="permission-name">${data.permission.name}</h6>
                            <div class="permission-actions">
                                <button class="btn btn-sm btn-info editBtn" data-id="${data.permission.id}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.permission.id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Created: ${data.permission.created_at.split('T')[0]}</small>
                    </div>
                </div>
                `;
                                const container = document.querySelector(
                                    `#collapse${data.permission.group_id} .accordion-body .row`);
                                if (id) {
                                    document.getElementById(`permission-${data.permission.id}`).outerHTML =
                                        cardHtml;
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
