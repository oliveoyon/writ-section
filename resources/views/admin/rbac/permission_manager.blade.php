@extends('dashboard.layouts.admin')
@section('title', 'Permission Manager')

@section('content')
    <div class="app-content-header mb-3">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Permission Manager</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Permission Manager</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-success btn-modern" id="addGroupBtn">
                    <i class="bi bi-plus-circle"></i> Add Group
                </button>
            </div>

            <div class="row g-3" id="groupsContainer">
                @foreach ($groups as $group)
                    <div class="col-md-3">
                        <div class="card shadow-sm group-card" data-id="{{ $group->id }}">
                            <div class="card-body text-center">
                                <h5 class="card-title">{{ $group->name }}</h5>
                                <p class="card-text text-muted">
                                    {{ $group->permissions_count }} Permissions
                                </p>
                                <div class="btn-group">
                                    <button class="btn btn-outline-primary btn-sm togglePermissions"
                                        data-id="{{ $group->id }}">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    <button class="btn btn-outline-info btn-sm editGroupBtn" data-id="{{ $group->id }}"
                                        data-name="{{ $group->name }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm deleteGroupBtn"
                                        data-id="{{ $group->id }}" data-name="{{ $group->name }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="permissions-list p-3 border-top d-none text-start"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Group Modal --}}
    <div class="modal fade" id="groupModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="groupForm">
                @csrf
                <input type="hidden" id="groupId">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Group</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="groupName" class="form-label">Name</label>
                            <input type="text" id="groupName" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Permission Modal --}}
    <div class="modal fade" id="permissionModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="permissionForm">
                @csrf
                <input type="hidden" id="permissionId">
                <input type="hidden" id="permissionGroupId">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Permission</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="permissionName" class="form-label">Name</label>
                            <input type="text" id="permissionName" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                let groupModal = new bootstrap.Modal(document.getElementById('groupModal'));
                let permModal = new bootstrap.Modal(document.getElementById('permissionModal'));

                // Add Group
                document.getElementById('addGroupBtn').addEventListener('click', () => {
                    document.getElementById('groupForm').reset();
                    document.getElementById('groupId').value = '';
                    groupModal.show();
                });

                // Save Group
                document.getElementById('groupForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    let id = document.getElementById('groupId').value;
                    let url = id ? `/admin/permission-manager/group/${id}` : `/admin/permission-manager/group`;
                    let method = id ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            name: document.getElementById('groupName').value
                        })
                    }).then(r => r.json()).then(res => {
                        if (res.success) {
                            Swal.fire('Success', res.message ?? 'Saved!', 'success').then(() => location
                                .reload());
                        }
                    });
                });

                // Edit Group
                document.querySelectorAll('.editGroupBtn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        let id = this.dataset.id;
                        let name = this.dataset.name;
                        document.getElementById('groupId').value = id;
                        document.getElementById('groupName').value = name;
                        groupModal.show();
                    });
                });

                // Delete Group
                document.querySelectorAll('.deleteGroupBtn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        let id = this.dataset.id;
                        let name = this.dataset.name;
                        Swal.fire({
                                title: `Delete group "${name}"?`,
                                text: "This action cannot be undone!",
                                icon: 'warning',
                                showCancelButton: true
                            })
                            .then(result => {
                                if (result.isConfirmed) {
                                    fetch(`/admin/permission-manager/group/${id}`, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        }
                                    }).then(r => r.json()).then(res => {
                                        if (res.success) {
                                            Swal.fire(`Group "${name}" deleted!`, '',
                                                'success').then(() => location.reload());
                                        }
                                    });
                                }
                            });
                    });
                });

                // Toggle Permissions
                document.querySelectorAll('.togglePermissions').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const container = this.closest('.group-card').querySelector(
                            '.permissions-list');
                        if (container.classList.contains('d-none')) {
                            fetch(`/admin/permission-manager/${id}/permissions`)
                                .then(r => r.json())
                                .then(data => {
                                    container.innerHTML = `<button class="btn btn-sm btn-success mb-2 addPermBtn" data-id="${id}">
                                        <i class="bi bi-plus"></i> Add Permission</button>`;
                                    if (data.length === 0) {
                                        container.innerHTML +=
                                            '<small class="text-muted">No permissions.</small>';
                                    } else {
                                        data.forEach(p => {
                                            container.innerHTML += `<div class="d-flex justify-content-between align-items-center mb-1">
                                                <span>${p.name}</span>
                                                <div>
                                                  <button class="btn btn-sm btn-info editPermBtn" data-id="${p.id}" data-name="${p.name}" data-group="${id}">
                                                    <i class="bi bi-pencil"></i>
                                                  </button>
                                                  <button class="btn btn-sm btn-danger delPermBtn" data-id="${p.id}" data-name="${p.name}">
                                                    <i class="bi bi-trash"></i>
                                                  </button>
                                                </div>
                                              </div>`;
                                        });
                                    }
                                    container.classList.remove('d-none');
                                    bindPermActions(container);
                                });
                        } else {
                            container.classList.add('d-none');
                        }
                    });
                });

                function bindPermActions(container) {
                    // Add Permission
                    container.querySelector('.addPermBtn')?.addEventListener('click', function() {
                        document.getElementById('permissionForm').reset();
                        document.getElementById('permissionId').value = '';
                        document.getElementById('permissionGroupId').value = this.dataset.id;
                        permModal.show();
                    });

                    // Edit Permission
                    container.querySelectorAll('.editPermBtn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            document.getElementById('permissionId').value = this.dataset.id;
                            document.getElementById('permissionName').value = this.dataset.name;
                            document.getElementById('permissionGroupId').value = this.dataset.group;
                            permModal.show();
                        });
                    });

                    // Delete Permission
                    container.querySelectorAll('.delPermBtn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            let id = this.dataset.id;
                            let name = this.dataset.name;
                            Swal.fire({
                                    title: `Delete permission "${name}"?`,
                                    text: "This action cannot be undone!",
                                    icon: 'warning',
                                    showCancelButton: true
                                })
                                .then(result => {
                                    if (result.isConfirmed) {
                                        fetch(`/admin/permission-manager/permission/${id}`, {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            }
                                        }).then(r => r.json()).then(res => {
                                            if (res.success) {
                                                Swal.fire(`Permission "${name}" deleted!`,
                                                    '', 'success').then(() => location
                                                    .reload());
                                            }
                                        });
                                    }
                                });
                        });
                    });
                }

                // Save Permission
                document.getElementById('permissionForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    let id = document.getElementById('permissionId').value;
                    let gid = document.getElementById('permissionGroupId').value;
                    let url = id ? `/admin/permission-manager/permission/${id}` :
                        `/admin/permission-manager/permission`;
                    let method = id ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            name: document.getElementById('permissionName').value,
                            group_id: gid
                        })
                    }).then(r => r.json()).then(res => {
                        if (res.success) {
                            Swal.fire('Success', res.message ?? 'Saved!', 'success').then(() => location
                                .reload());
                        }
                    });
                });
            });
        </script>
    @endpush

    <style>
        /* Group & Permission Cards */
        .group-card {
            border: 1px solid #dceee4;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .group-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
        }

        /* Upper header section */
        .group-card .card-body {
            background: #e6f4ea;
            /* soft greenish top */
            padding: 1rem;
        }

        /* Lower permissions section */
        .group-card .permissions-list {
            background: #fff;
            /* keep permissions on white */
            border-top: 1px solid #dceee4;
            padding: 1rem;
        }

        /* Card titles */
        .group-card h5 {
            color: #2e7d32;
            font-weight: 600;
        }

        /* Buttons group */
        .btn-soft {
            border: none;
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            transition: background 0.25s ease-in-out;
        }

        .btn-soft-info {
            background: #e6f3ff;
            color: #0277bd;
        }

        .btn-soft-info:hover {
            background: #cce7ff;
            color: #015a8a;
        }

        .btn-soft-danger {
            background: #ffeaea;
            color: #c62828;
        }

        .btn-soft-danger:hover {
            background: #ffcdd2;
            color: #b71c1c;
        }

        /* Collapse transition */
        .details-collapse {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: max-height 0.4s ease, opacity 0.4s ease;
        }

        .details-collapse.show {
            max-height: 500px;
            opacity: 1;
        }
    </style>

@endsection
