@extends('admin.layouts.adminlayout')

@section('content')
    @php
        $systemDepartmentCount = $departments->whereIn('name', \App\Models\Department::CANONICAL_NAMES)->count();
        $customDepartmentCount = $departments->count() - $systemDepartmentCount;
    @endphp

    <div class="container py-4 department-page">

        <div class="page-heading mb-3">
            <div>
                <div class="system-mark">RTFTS Setup</div>
                <h4 class="mb-0">Departments</h4>
                <small>Manage section names shown to staff while keeping system names stable.</small>
            </div>
            <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                <i class="bi bi-plus-circle"></i> Add
            </button>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="summary-box">
                    <i class="bi bi-diagram-3"></i>
                    <span>Total Departments</span>
                    <strong>{{ $departments->count() }}</strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-box system">
                    <i class="bi bi-shield-lock"></i>
                    <span>System Departments</span>
                    <strong>{{ $systemDepartmentCount }}</strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-box custom">
                    <i class="bi bi-plus-square"></i>
                    <span>Custom Departments</span>
                    <strong>{{ $customDepartmentCount }}</strong>
                </div>
            </div>
        </div>

        <div class="admin-panel mb-3">
            <div class="panel-heading">
                <div>
                    <h5>Department List</h5>
                    <span>Display name can be changed; system name stays for application logic.</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle modern-table mb-0">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>Display Name</th>
                            <th>System Name</th>
                            <th width="120">Type</th>
                            <th width="130" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($departments as $index => $department)
                            <tr id="row-{{ $department->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold text-dark">{{ $department->label }}</td>
                                <td><code>{{ $department->name }}</code></td>
                                <td>
                                    @if(in_array($department->name, \App\Models\Department::CANONICAL_NAMES, true))
                                        <span class="type-badge system">System</span>
                                    @else
                                        <span class="type-badge custom">Custom</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-action edit-btn" title="Edit display name">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    @unless(in_array($department->name, \App\Models\Department::CANONICAL_NAMES, true))
                                        <button class="btn btn-action danger delete-btn" title="Delete department">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-panel role-panel">
            <div class="panel-heading">
                <div>
                    <h5>Role Display Names</h5>
                    <span>Rename roles for staff-friendly menus without changing permissions.</span>
                </div>
            </div>
            @foreach($roles as $role)
                <form class="role-label-form"
                      action="{{ route('admin.roles.display-name.update', $role) }}"
                      method="POST">
                    @csrf
                    @method('PUT')
                    <div class="role-system-name">
                        <span>System Name</span>
                        <strong>{{ $role->name }}</strong>
                    </div>
                    <div class="role-display-name">
                        <input class="form-control" name="display_name" value="{{ $role->display_name ?: $role->name }}" required>
                    </div>
                    <div>
                        <button class="btn btn-gold btn-sm" type="submit">Update</button>
                    </div>
                </form>
            @endforeach
        </div>

    </div>

    <!-- Add Department Modal -->
    <div class="modal fade" id="addDepartmentModal">
        <div class="modal-dialog">
            <form id="addDepartmentForm">
                @csrf
                <div class="modal-content modal-panel">

                    <div class="modal-header">
                        <h5 class="modal-title">Add Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label">Department Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-gold">Save</button>
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

                <div class="modal-content modal-panel">

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label">Display Name</label>
                        <input type="text" class="form-control" name="display_name" id="edit_name" required>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-gold">Update</button>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .department-page { max-width: 1120px; }
        .page-heading {
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
        .page-heading h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
        .page-heading small { color: #6b7280; font-weight: 600; }
        .system-mark { color: #b87d08; font-size: .82rem; font-weight: 800; }
        .summary-box {
            position: relative;
            min-height: 94px;
            padding: .85rem .9rem;
            background: #fff;
            border: 1px solid #e3e8ef;
            border-left: 4px solid #00284d;
            border-radius: 4px;
            box-shadow: 0 1px 5px rgba(0, 40, 77, .06);
        }
        .summary-box.system { border-left-color: #087587; }
        .summary-box.custom { border-left-color: #d4a017; }
        .summary-box i { position: absolute; right: .85rem; top: .85rem; color: #d4a017; font-size: 1.35rem; }
        .summary-box span { display: block; color: #6b7280; font-size: .82rem; font-weight: 800; text-transform: uppercase; }
        .summary-box strong { display: block; color: #111827; font-size: 2rem; line-height: 1.1; margin-top: .45rem; }
        .admin-panel {
            background: #fff;
            border: 1px solid #e3e8ef;
            border-radius: 4px;
            box-shadow: 0 1px 5px rgba(0, 40, 77, .07);
            overflow: hidden;
        }
        .panel-heading {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: .75rem 1rem;
            background: #fbfcfe;
            border-top: 3px solid #00284d;
            border-bottom: 1px solid #e5e7eb;
        }
        .panel-heading h5 { margin: 0; color: #1f2937; font-size: 1rem; font-weight: 800; }
        .panel-heading span { color: #6b7280; font-size: .84rem; font-weight: 600; }
        table.modern-table th,
        table.modern-table td { padding: .72rem .9rem; vertical-align: middle; }
        table.modern-table thead th {
            background: #eef5fb;
            color: #00284d;
            border-bottom: 0;
            font-weight: 800;
            white-space: nowrap;
        }
        table.modern-table tbody tr:hover { background: #fffaf0; }
        table.modern-table code {
            color: #0b4f8a;
            background: #f2f7fc;
            border: 1px solid #d8e3ef;
            border-radius: 4px;
            padding: .18rem .4rem;
            font-size: .82rem;
        }
        .type-badge {
            display: inline-flex;
            border-radius: 4px;
            padding: .2rem .5rem;
            font-size: .76rem;
            font-weight: 800;
            border: 1px solid #d8e3ef;
            background: #f7fbff;
            color: #0b4f8a;
        }
        .type-badge.custom { background: #fff7e6; border-color: #f3d58e; color: #805500; }
        .btn-gold {
            background-color: #d4a017;
            color: #111827;
            border: 1px solid #c89313;
            border-radius: 4px;
            font-weight: 800;
        }
        .btn-gold:hover { background-color: #b38b0f; color: #fff; }
        .btn-action {
            width: 2rem;
            height: 2rem;
            display: inline-grid;
            place-items: center;
            padding: 0;
            border-radius: 4px;
            border: 1px solid #bcd0e2;
            color: #0b4f8a;
            background: #f2f7fc;
        }
        .btn-action:hover { background: #0b4f8a; color: #fff; }
        .btn-action.danger { border-color: #f1b7b7; color: #a93b2d; background: #fff5f5; }
        .btn-action.danger:hover { background: #a93b2d; color: #fff; }
        .role-panel { padding-bottom: .65rem; }
        .role-label-form {
            display: grid;
            grid-template-columns: minmax(180px, 260px) 1fr auto;
            gap: .75rem;
            align-items: center;
            padding: .75rem 1rem;
            border-bottom: 1px solid #eef2f7;
        }
        .role-label-form:last-child { border-bottom: 0; }
        .role-system-name span { display: block; color: #6b7280; font-size: .76rem; font-weight: 800; text-transform: uppercase; }
        .role-system-name strong { color: #1f2937; font-size: .9rem; }
        .role-display-name .form-control,
        .modal-panel .form-control { border-radius: 4px; }
        .role-display-name .form-control:focus,
        .modal-panel .form-control:focus { border-color: #d4a017; box-shadow: 0 0 0 .15rem rgba(212, 160, 23, .15); }
        .modal-panel { border: 0; border-radius: 4px; overflow: hidden; }
        .modal-panel .modal-header {
            background: #00284d;
            color: #fff;
            border-bottom: 3px solid #d4a017;
        }
        .modal-panel .modal-title { color: #fff; font-weight: 800; }
        .modal-panel .modal-body,
        .modal-panel .modal-footer { padding: 1rem; }
        @media (max-width: 768px) {
            .page-heading { align-items: stretch; flex-direction: column; }
            .page-heading .btn { width: 100%; }
            .role-label-form { grid-template-columns: 1fr; }
            .role-label-form .btn { width: 100%; }
            table.modern-table { min-width: 680px; }
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
                            document.getElementById("edit_name").value = data.display_name || data.name;
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

            document.querySelectorAll(".role-label-form").forEach(form => {
                form.addEventListener("submit", function(e) {
                    e.preventDefault();

                    fetch(this.action, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "X-HTTP-Method-Override": "PUT"
                        },
                        body: new FormData(this)
                    }).then(res => res.json())
                    .then(data => Swal.fire({
                        title: data.success ? 'Updated' : 'Error',
                        text: data.message,
                        icon: data.success ? 'success' : 'error',
                        confirmButtonColor: '#d4a017'
                    }));
                });
            });

        });
    </script>
@endpush
