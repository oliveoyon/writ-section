@extends('dashboard.layouts.admin-layout')

@section('title', 'Role Management')



@section('content')
    <section>
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col">
                    <button class="btn btn-success btn-sm" id="createRoleBtn"><i class="fas fa-plus-square mr-1"></i> Create
                        Role</button>
                </div>
            </div>

            <!-- Roles Table -->
            <table class="table table-striped" id="rolesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- This is where you will loop through your roles -->
                    @foreach ($roles as $role)
                        <tr id="role-{{ $role->id }}">
                            <td>{{ $loop->iteration }} </td>
                            <td>{{ $role->name }}</td>
                            <td>
                                <button class="btn btn-warning btn-sm editRoleBtn" data-id="{{ $role->id }}"
                                    data-name="{{ $role->name }}">Edit</button>
                                <button class="btn btn-danger btn-sm deleteRoleBtn"
                                    data-id="{{ $role->id }}">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Fullscreen Modal for Create/Edit Role -->
        <div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="roleModalLabel">Add New Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="roleForm" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="roleName" class="form-label">Role Name</label>
                                <input type="text" class="form-control" id="roleName" name="name">
                            </div>
                            <div class="mb-3 text-end custombtn">
                                <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.getElementById('createRoleBtn').addEventListener('click', function() {
            document.getElementById('roleForm').reset();
            document.getElementById('roleForm').setAttribute('action', '{{ route('roles.add') }}');
            document.getElementById('roleForm').setAttribute('method', 'POST');
            document.getElementById('roleModalLabel').textContent = 'Add New Role';
            var roleModal = new bootstrap.Modal(document.getElementById('roleModal'));
            roleModal.show();
        });

        document.querySelectorAll('.editRoleBtn').forEach(function(button) {
            button.addEventListener('click', function() {
                var roleId = this.getAttribute('data-id');
                var roleName = this.getAttribute('data-name');

                document.getElementById('roleName').value = roleName;
                document.getElementById('roleModalLabel').textContent = 'Edit Role';
                document.getElementById('roleForm').setAttribute('action',
                    '{{ route('roles.update', ':roleId') }}'.replace(':roleId', roleId));
                document.getElementById('roleForm').setAttribute('method', 'POST');

                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_method';
                input.value = 'PUT';
                document.getElementById('roleForm').appendChild(input);

                var roleModal = new bootstrap.Modal(document.getElementById('roleModal'));
                roleModal.show();
            });
        });

        document.querySelectorAll('.deleteRoleBtn').forEach(function(button) {
            button.addEventListener('click', function() {
                var roleId = this.getAttribute('data-id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You will not be able to recover this role!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {

                        fetch('{{ route('roles.delete', ':roleId') }}'.replace(
                                ':roleId', roleId), {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })


                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    document.getElementById('role-' + roleId).remove();

                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: 'The role has been deleted.',
                                        icon: 'success',
                                        position: 'top-end',
                                        toast: true,
                                        showConfirmButton: false,
                                        timer: 2000,
                                        timerProgressBar: true,
                                    });
                                } else {
                                    Swal.fire('Error!', 'There was an error deleting the role.',
                                        'error');
                                }
                            });
                    }
                });
            });
        });

        document.getElementById('roleForm').addEventListener('submit', function(event) {
            event.preventDefault();

            var submitButton = document.querySelector('#submitBtn');
            submitButton.disabled = true;

            var action = this.getAttribute('action');
            var method = this.getAttribute('method');
            var formData = new FormData(this);
            var roleModalElement = document.getElementById('roleModal');
            var roleModal = bootstrap.Modal.getInstance(roleModalElement);

            fetch(action, {
                    method: method,
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (roleModal) {
                            roleModal.hide(); // Hide modal first
                        }

                        // Show Swal message for at least 2 seconds
                        let swalInstance = Swal.fire({
                            title: 'Success!',
                            text: method === 'POST' ? 'Role added successfully.' :
                                'Role updated successfully.',
                            icon: 'success',
                            position: 'top-end',
                            toast: true,
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                        });

                        // Update UI instantly
                        setTimeout(() => {
                            if (method === 'POST') {
                                location.reload(); // Reload page after Swal message finishes
                            } else {
                                let roleRow = document.getElementById('role-' + data.id);
                                if (roleRow) {
                                    roleRow.querySelector('.role-name').textContent = formData.get(
                                        'role_name');
                                }
                            }
                        }, 500); // Delay UI update slightly for smoothness

                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'There was an error processing your request.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire('Error!', 'Something went wrong!', 'error');
                })
                .finally(() => {
                    submitButton.disabled = false;
                });
        });
    </script>
@endpush
