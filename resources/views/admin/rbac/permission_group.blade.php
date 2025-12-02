@extends('admin.layouts.adminlayout')

@section('content')
    <div class="container py-4">

        <!-- Header Card -->
        <div class="lawyer-card d-flex justify-content-between align-items-center mb-3 p-3"
            style="background:#fff; color:#00284d;">
            <h4 class="mb-0 profile-section-title">Permission Groups</h4>

            @can('Create Permission Group')
                <button class="btn btn-gold btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addGroupModal">
                    <i class="bi bi-plus-circle"></i> Add Group
                </button>
            @endcan
        </div>


        <!-- Table Card -->
        <div class="lawyer-card p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle modern-table">
                    <thead>
                        <tr style="background:#00284d; color:#fff;">
                            <th width="60">#</th>
                            <th>Name</th>
                            <th>Total Permissions</th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groups as $index => $group)
                            <tr id="row-{{ $group->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold text-dark">{{ $group->name }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $group->permissions_count }}</span>
                                </td>
                                <td>
                                    @can('Edit Permission Group')
                                        <button class="btn btn-info btn-sm me-1 edit-btn">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endcan
                                    @can('Delete Permission Group')
                                        <button class="btn btn-danger btn-sm delete-btn">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Add Group Modal -->
    <div class="modal fade" id="addGroupModal">
        <div class="modal-dialog">
            <form id="addGroupForm">
                @csrf
                <div class="modal-content lawyer-card p-3">

                    <div class="modal-header border-0">
                        <h5 class="profile-section-title">Add Permission Group</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label fw-bold">Group Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-gold px-4">Save</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Edit Group Modal -->
    <div class="modal fade" id="editGroupModal">
        <div class="modal-dialog">
            <form id="editGroupForm">
                @csrf
                <input type="hidden" name="group_id" id="edit_id">

                <div class="modal-content lawyer-card p-3">

                    <div class="modal-header border-0">
                        <h5 class="profile-section-title">Edit Permission Group</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label fw-bold">Group Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button class="btn btn-gold px-4">Update</button>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endsection

@push('css')
    <style>
        main {
            padding-top: 40px !important;
        }

        .profile-section-title {
            color: #003366;
            font-weight: 700;
        }

        .modern-table tbody tr:hover {
            background: rgba(212, 160, 23, 0.1);
        }

        .btn-gold {
            background: #d4a017;
            color: #00284d;
            font-weight: 600;
            border-radius: 8px;
            transition: 0.3s;
        }

        .btn-gold:hover {
            background: #c39b15;
            color: #fff;
        }

        .btn-info {
            background: #007bff;
            border-radius: 6px;
            transition: 0.3s;
        }

        .btn-info:hover {
            background: #0056b3;
            color: #fff;
        }

        .btn-danger {
            background: #dc3545;
            border-radius: 6px;
            transition: 0.3s;
        }

        .btn-danger:hover {
            background: #a71d2a;
            color: #fff;
        }

        .badge {
            font-size: 0.875rem;
        }

        .lawyer-card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }
    </style>
@endpush

@push('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // ADD group
            document.getElementById("addGroupForm").addEventListener("submit", function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                fetch("{{ route('admin.permission-groups.store') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: formData
                    }).then(res => res.json())
                    .then(data => {
                        if (data.success) Swal.fire({
                            title: 'Success',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#d4a017'
                        }).then(() => location.reload());
                        else Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            confirmButtonColor: '#d4a017'
                        });
                    });
            });

            // OPEN edit modal
            document.querySelectorAll(".edit-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    fetch(`/admin/permission-groups/${this.closest('tr').id.split('-')[1]}/edit`)
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById("edit_id").value = data.id;
                            document.getElementById("edit_name").value = data.name;
                            new bootstrap.Modal(document.getElementById("editGroupModal"))
                            .show();
                        });
                });
            });

            // UPDATE group
            document.getElementById("editGroupForm").addEventListener("submit", function(e) {
                e.preventDefault();
                let id = document.getElementById("edit_id").value;
                let formData = new FormData(this);
                fetch(`/admin/permission-groups/${id}`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "X-HTTP-Method-Override": "PUT"
                        },
                        body: formData
                    }).then(res => res.json())
                    .then(data => {
                        if (data.success) Swal.fire({
                            title: 'Updated',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#d4a017'
                        }).then(() => location.reload());
                        else Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            confirmButtonColor: '#d4a017'
                        });
                    });
            });

            // DELETE group
            document.querySelectorAll(".delete-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    let id = this.closest('tr').id.split('-')[1];
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d4a017',
                        cancelButtonColor: '#00284d',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (!result.isConfirmed) return;
                        fetch(`/admin/permission-groups/${id}`, {
                                method: "POST",
                                headers: {
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                    "X-HTTP-Method-Override": "DELETE"
                                }
                            }).then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: data.message,
                                        icon: 'success',
                                        confirmButtonColor: '#d4a017'
                                    });
                                    document.getElementById("row-" + id).remove();
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: data.message,
                                        icon: 'error',
                                        confirmButtonColor: '#d4a017'
                                    });
                                }
                            });
                    });
                });
            });

        });
    </script>
@endpush
