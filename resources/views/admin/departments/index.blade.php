@extends('admin.layouts.adminlayout')

@section('content')
    <div class="container py-4">

        <!-- Header Card -->
        <div class="department-card d-flex justify-content-between align-items-center mb-3 p-2"
            style="background:#00284d; color:#fff;">
            <h4 class="mb-0 profile-section-title">Departments</h4>

            <button class="btn btn-gold btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                <i class="bi bi-plus-circle"></i> Add Department
            </button>
        </div>

        <!-- Table Card -->
        <div class="department-card p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle modern-table">
                    <thead>
                        <tr style="background:#00284d; color:#fff;">
                            <th width="60">#</th>
                            <th>Name</th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($departments as $index => $department)
                            <tr id="row-{{ $department->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold text-dark">{{ $department->name }}</td>
                                <td>
                                    <button class="btn btn-info btn-sm me-1 edit-btn">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-btn">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Add Department Modal -->
    <div class="modal fade" id="addDepartmentModal">
        <div class="modal-dialog">
            <form id="addDepartmentForm">
                @csrf
                <div class="modal-content department-card p-3">

                    <div class="modal-header border-0">
                        <h5>Add Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label fw-bold">Department Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-gold px-4">Save</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Edit Department Modal -->
    <div class="modal fade" id="editDepartmentModal">
        <div class="modal-dialog">
            <form id="editDepartmentForm">
                @csrf
                <input type="hidden" name="department_id" id="edit_id">

                <div class="modal-content department-card p-3">

                    <div class="modal-header border-0">
                        <h5>Edit Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label fw-bold">Department Name</label>
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
        /* Main padding */
        main {
            padding-top: 40px !important;
        }

        /* Section Titles */
        .profile-section-title {
            color: #ffffff;
            /* main text color for titles */
            font-weight: 700;
        }

        .modal-title {
            color: rgba(0, 0, 0, 0.08);
        }

      
        /* Card wrapper */
        .department-card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            background: #fff;
            padding: 1rem;
        }

        /* Table styles */
        table.modern-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.modern-table th,
        table.modern-table td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
        }

        table.modern-table thead tr {
            background: #00284d;
            color: #fff;
        }

        table.modern-table tbody tr:hover {
            background: rgba(212, 160, 23, 0.1);
        }

        /* Badges */
        .badge {
            font-size: 0.875rem;
        }

        /* Buttons */
        .btn-gold {
            background-color: #d4a017;
            color: #fff;
            border-radius: 8px;
            font-weight: 500;
        }

        .btn-gold:hover {
            background-color: #b38b0f;
        }

        .btn-info {
            background: #007bff;
            border-radius: 6px;
            transition: 0.3s;
            color: #fff;
        }

        .btn-info:hover {
            background: #0056b3;
            color: #fff;
        }

        .btn-danger {
            background: #dc3545;
            border-radius: 6px;
            transition: 0.3s;
            color: #fff;
        }

        .btn-danger:hover {
            background: #a71d2a;
            color: #fff;
        }

        /* Role/Permission action buttons */
        .role-actions button,
        .permission-actions button {
            border-radius: 6px;
            margin-left: 4px;
            font-size: 0.85rem;
        }

        /* Modal content */
        .modal-content.department-card {
            padding: 1rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .department-card {
                padding: 0.75rem;
            }

            .btn-gold,
            .btn-info,
            .btn-danger {
                font-size: 0.8rem;
                padding: 0.35rem 0.65rem;
            }

            table.modern-table th,
            table.modern-table td {
                padding: 0.5rem 0.75rem;
            }
        }
    </style>
@endpush

@push('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // ADD Department
            document.getElementById("addDepartmentForm").addEventListener("submit", function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                fetch("{{ route('admin.departments.store') }}", {
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
                    fetch(`/admin/departments/${this.closest('tr').id.split('-')[1]}/edit`)
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById("edit_id").value = data.id;
                            document.getElementById("edit_name").value = data.name;
                            new bootstrap.Modal(document.getElementById("editDepartmentModal"))
                                .show();
                        });
                });
            });

            // UPDATE Department
            document.getElementById("editDepartmentForm").addEventListener("submit", function(e) {
                e.preventDefault();
                let id = document.getElementById("edit_id").value;
                let formData = new FormData(this);
                fetch(`/admin/departments/${id}`, {
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

            // DELETE Department
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
                        fetch(`/admin/departments/${id}`, {
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
