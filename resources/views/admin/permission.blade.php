@extends('dashboard.layouts.admin-layout')

@section('title', 'Permission Management')

@section('content')
    <section>
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col">
                    <button class="btn btn-success btn-sm" id="createPermissionBtn"><i class="fas fa-plus-square mr-1"></i> Create Permission</button>
                </div>
            </div>

            <!-- Permissions Table -->
            <table class="table table-striped" id="permissionsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Category</th> <!-- Added Category Column -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $permission)
                        <tr id="permission-{{ $permission->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $permission->name }}</td>
                            <td>{{ $permission->category }}</td> <!-- Display Category -->
                            <td>
                                <button class="btn btn-warning btn-sm editPermissionBtn" data-id="{{ $permission->id }}" data-name="{{ $permission->name }}" data-category="{{ $permission->category }}">Edit</button>
                                <button class="btn btn-danger btn-sm deletePermissionBtn" data-id="{{ $permission->id }}">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Modal for Create/Edit Permission -->
        <div class="modal fade" id="permissionModal" tabindex="-1" aria-labelledby="permissionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="permissionModalLabel">Add New Permission</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="permissionForm" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="permissionName" class="form-label">Permission Name</label>
                                <input type="text" class="form-control" id="permissionName" name="name" required>
                            </div>

                            <!-- Category Dropdown -->
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-control" id="category" name="category" required>
                                <option value="">Select a Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                                @endforeach
                                    <!-- Add more categories as needed -->
                                </select>
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
    document.getElementById('createPermissionBtn').addEventListener('click', function() {
        document.getElementById('permissionForm').reset();
        document.getElementById('permissionForm').setAttribute('action', '{{ route('permissions.add') }}');
        document.getElementById('permissionForm').setAttribute('method', 'POST');
        document.getElementById('permissionModalLabel').textContent = 'Add New Permission';
        var permissionModal = new bootstrap.Modal(document.getElementById('permissionModal'));
        permissionModal.show();
    });

    document.querySelectorAll('.editPermissionBtn').forEach(function(button) {
        button.addEventListener('click', function() {
            var permissionId = this.getAttribute('data-id');
            var permissionName = this.getAttribute('data-name');
            var permissionCategory = this.getAttribute('data-category');

            document.getElementById('permissionName').value = permissionName;
            document.getElementById('category').value = permissionCategory; // Set selected category
            document.getElementById('permissionModalLabel').textContent = 'Edit Permission';
            document.getElementById('permissionForm').setAttribute('action',
                '{{ route('permissions.update', ':permissionId') }}'.replace(':permissionId', permissionId));
            document.getElementById('permissionForm').setAttribute('method', 'POST');

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_method';
            input.value = 'PUT';
            document.getElementById('permissionForm').appendChild(input);

            var permissionModal = new bootstrap.Modal(document.getElementById('permissionModal'));
            permissionModal.show();
        });
    });

    document.querySelectorAll('.deletePermissionBtn').forEach(function(button) {
        button.addEventListener('click', function() {
            var permissionId = this.getAttribute('data-id');

            Swal.fire({
                title: 'Are you sure?',
                text: 'This permission will be permanently deleted!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route('permissions.delete', ':permissionId') }}'.replace(':permissionId', permissionId), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('permission-' + permissionId).remove();

                            Swal.fire({
                                title: 'Deleted!',
                                text: 'The permission has been deleted.',
                                icon: 'success',
                                position: 'top-end',
                                toast: true,
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true,
                            });
                        } else {
                            Swal.fire('Error!', 'There was an error deleting the permission.', 'error');
                        }
                    });
                }
            });
        });
    });

    document.getElementById('permissionForm').addEventListener('submit', function(event) {
        event.preventDefault();

        var submitButton = document.querySelector('#submitBtn');
        submitButton.disabled = true;

        var action = this.getAttribute('action');
        var method = this.getAttribute('method');
        var formData = new FormData(this);
        var permissionModalElement = document.getElementById('permissionModal');
        var permissionModal = bootstrap.Modal.getInstance(permissionModalElement);

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
                if (permissionModal) {
                    permissionModal.hide(); // Hide modal first
                }

                // Show Swal message for at least 2 seconds
                let swalInstance = Swal.fire({
                    title: 'Success!',
                    text: method === 'POST' ? 'Permission added successfully.' : 'Permission updated successfully.',
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
                        let permissionRow = document.getElementById('permission-' + data.id);
                        if (permissionRow) {
                            permissionRow.querySelector('.permission-name').textContent = formData.get('permission_name');
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
