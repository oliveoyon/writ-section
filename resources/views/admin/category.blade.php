@extends('dashboard.layouts.admin-layout')

@section('title', 'Category Management')

@section('content')

    <!-- Page Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Category Management</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Categories</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Content -->
    <div class="app-content">
        <div class="container-fluid">

            <!-- Action Button -->
            <div class="row mb-3">
                <div class="col">
                    <button class="btn btn-success btn-sm" id="createDistrictBtn">
                        <i class="fas fa-plus-square me-1"></i> Add New Category
                    </button>
                </div>
            </div>

            <!-- Categories Table -->
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-striped align-middle" id="districtsTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Name</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr id="category-{{ $category->id }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="category-name">{{ $category->name }}</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm editDistrictBtn"
                                            data-id="{{ $category->id }}"
                                            data-name="{{ $category->name }}">
                                            Edit
                                        </button>
                                        <button class="btn btn-danger btn-sm deleteDistrictBtn"
                                            data-id="{{ $category->id }}">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Fullscreen Modal for Create/Edit Category -->
    <div class="modal fade" id="districtModal" tabindex="-1" aria-labelledby="districtModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="districtModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="districtForm" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="districtName" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="districtName" name="name">
                        </div>
                        <div class="mb-3 text-end custombtn">
                            <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Create Category
    document.getElementById('createDistrictBtn').addEventListener('click', function() {
        document.getElementById('districtForm').reset();
        document.getElementById('districtForm').setAttribute('action', '{{ route('categories.add') }}');
        document.getElementById('districtForm').setAttribute('method', 'POST');
        document.getElementById('districtModalLabel').textContent = 'Add New Category';
        var districtModal = new bootstrap.Modal(document.getElementById('districtModal'));
        districtModal.show();
    });

    // Edit Category
    document.querySelectorAll('.editDistrictBtn').forEach(function(button) {
        button.addEventListener('click', function() {
            var districtId = this.getAttribute('data-id');
            var districtName = this.getAttribute('data-name');

            document.getElementById('districtName').value = districtName;
            document.getElementById('districtModalLabel').textContent = 'Edit Category';
            document.getElementById('districtForm').setAttribute('action',
                '{{ route('categories.update', ':districtId') }}'.replace(':districtId', districtId));
            document.getElementById('districtForm').setAttribute('method', 'POST');

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_method';
            input.value = 'PUT';
            document.getElementById('districtForm').appendChild(input);

            var districtModal = new bootstrap.Modal(document.getElementById('districtModal'));
            districtModal.show();
        });
    });

    // Delete Category
    document.querySelectorAll('.deleteDistrictBtn').forEach(function(button) {
        button.addEventListener('click', function() {
            var districtId = this.getAttribute('data-id');

            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this category!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route('categories.delete', ':districtId') }}'.replace(':districtId', districtId), {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('category-' + districtId).remove();
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'The category has been deleted.',
                                    icon: 'success',
                                    position: 'top-end',
                                    toast: true,
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true,
                                });
                            } else {
                                Swal.fire('Error!', 'There was an error deleting the category.', 'error');
                            }
                        });
                }
            });
        });
    });

    // Save Category (Create/Update)
    document.getElementById('districtForm').addEventListener('submit', function(event) {
        event.preventDefault();

        var submitButton = document.querySelector('#submitBtn');
        submitButton.disabled = true;

        var action = this.getAttribute('action');
        var method = this.getAttribute('method');
        var formData = new FormData(this);
        var districtModalElement = document.getElementById('districtModal');
        var districtModal = bootstrap.Modal.getInstance(districtModalElement);

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
                    if (districtModal) {
                        districtModal.hide();
                    }
                    Swal.fire({
                        title: 'Success!',
                        text: method === 'POST' ? 'Category added successfully.' : 'Category updated successfully.',
                        icon: 'success',
                        position: 'top-end',
                        toast: true,
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                    setTimeout(() => {
                        location.reload();
                    }, 500);
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
