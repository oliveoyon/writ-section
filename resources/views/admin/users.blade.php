@extends('dashboard.layouts.admin-layout')

@section('title', 'User Management')



@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.2.0/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .modal-body {
        overflow-y: auto;
        max-height: 90vh;
        /* Keeps content scrollable within the full-screen modal */
    }

    /* Styling for each category box */
    .category-box {
        padding: 20px;
        background-color: #ffffff;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        /* Add space between categories */
    }

    /* Category Header Styling */
    .category-header {
        font-size: 18px;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 15px;
    }

    /* Permissions Grid inside each category */
    .permissions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 15px;
        /* Space between permission items */
    }

    /* Individual Permission Item Styling */
    .permission-item {
        padding: 10px 15px;
        background-color: #f4f7fc;
        border-left: 5px solid #4CAF50;
        margin-bottom: 10px;
        color: #333;
        font-size: 15px;
        border-radius: 8px;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .permission-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 12px rgba(0, 0, 0, 0.1);
    }

    /* Custom Styling for the Edit Permissions Form */
    .edit-permission-header {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #2c3e50;
    }

    .category-box {
        background-color: #f9fafb;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }



    /* Ensuring the grid remains neat on smaller screens */
    @media (max-width: 768px) {
        .permissions-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }
    }

    /* Classic Checkbox Style */
    .classic-checkbox {
        width: 16px;
        height: 16px;
        margin-right: 8px;
        /* Space between checkbox and label */
        vertical-align: middle;
        /* Align checkbox with the text */
        flex-shrink: 0;
        /* Prevent checkbox from shrinking */
    }

    /* Label Style for Classic Checkbox */
    .classic-checkbox-label {
        font-size: 14px;
        /* Text size */
        vertical-align: middle;
        /* Align label text with checkbox */
    }

    /* Category Title */
    .classic-category-title {
        font-size: 18px;
        font-weight: bold;
        color: #e74c3c;
        margin-bottom: 10px;
    }

    /* Grid Layout for Permission Items */
    .classic-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        /* Auto-fit columns with a minimum width of 120px */
        gap: 16px;
        /* Space between checkboxes */
        margin-bottom: 20px;
        /* Space between categories */
    }

    /* Permission Items */
    .form-check {
        display: flex;
        align-items: center;
        /* Vertically align checkbox with label */
        justify-content: flex-start;
        /* Align items to the start */
    }

    /* Column Control for Grid Layout */
    .col-md-4 {
        display: flex;
        justify-content: flex-start;
        width: 100%;
        /* Ensure items fit in the grid */
        align-items: center;
        /* Vertically align content in the column */
    }

    /* Make sure the checkboxes and labels have the same height */
    .form-check input[type="checkbox"] {
        height: 16px;
        width: 16px;
    }

    /* Optional: Adjust the label size */
    .form-check label {
        font-size: 14px;
        padding-left: 5px;
        /* Space between checkbox and label */
    }

    /* Make sure the items have consistent width */
    .classic-row .col-md-4 {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        text-align: left;
        width: 100%;
        flex: 1 1 auto;
        padding: 5px;
    }
</style>
@endpush


@section('content')
<section>
    <div class="container-fluid table-responsive">
        <div class="row mb-3">
            <div class="col">
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus-square mr-1"></i> Add User
                </button>
            </div>
        </div>


        <div class="alert alert-danger" id="errorAlert" style="display: none;">
            <ul id="errorList">
                <!-- Error messages will be inserted here dynamically -->
            </ul>
        </div>
        <table class="table table-bordered table-striped table-hover table-sm" id="user-table">
            <thead style="border-top: 1px solid #b4b4b4">
                <th style="width: 10px">#</th>
                <th>User Name</th>
                <th>Email</th>
                <th>District</th>
                <th>PNGO</th>
                <th>Status</th>
                <th style="width: 40px">Action</th>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->district ? $user->district->name : 'No District' }}</td>
                    <td>{{ $user->pngo ? $user->pngo->name : 'No PNGO' }}</td>
                    <td>
                        <span class="badge {{ $user->status == 1 ? 'bg-success' : 'bg-secondary' }}">
                            {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                        </span>
                    </td>

                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-info btn-sm" data-id="{{ $user->id }}"
                                id="editUserBtn">
                                <i class="fas fa-edit"></i> Edit
                            </button>

                            <!-- Button to trigger the modal -->
                            <button type="button" class="btn btn-success btn-sm view-permissions"
                                data-toggle="modal" data-id="{{ $user->id }}">
                                <i class="fas fa-eye"></i> View Permissions
                            </button>
                            <button class="btn btn-default btn-sm edit-user-permissions"
                                data-id="{{ $user->id }}" data-name="{{ $user->name }}">
                                <i class="fas fa-pencil-alt"></i> Edit Permissions
                            </button>
                        </div>
                    </td>

                </tr>
                @endforeach

            </tbody>
        </table>

        <!-- Modal -->
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('addUser') }}" method="POST" autocomplete="off" id="add-user-form">
                            @csrf
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label required" for="name">User Name</label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="Enter name">
                                    <span class="text-danger error-text name_error"></span>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label required" for="email">Email</label>
                                    <input type="email" class="form-control" name="email" id="email" placeholder="Enter email">
                                    <span class="text-danger error-text email_error"></span>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label required" for="district_id">District</label>
                                    <select class="form-control" name="district_id" id="district_id">
                                        <option value="">Select District</option>
                                        @foreach ($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger error-text district_id_error"></span>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label required" for="pngo_id">PNGO</label>
                                    <select class="form-control" name="pngo_id" id="pngo_id">
                                        <option value="">Select PNGO</option>
                                        @foreach ($pngos as $pngo)
                                        <option value="{{ $pngo->id }}">{{ $pngo->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger error-text pngo_id_error"></span>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label required" for="role_name">Roles</label>
                                    <select class="form-control" name="role_name[]" id="role_name" multiple>
                                        <option value="">Select Role (Multiple)</option>
                                        @foreach ($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger error-text role_name_error"></span>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label required" for="status">Status</label>
                                    <select class="form-control" name="status" id="status">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <span class="text-danger error-text status_error"></span>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>



        <div class="modal fade editUser" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form action="{{ route('updateUserDetails') }}" method="post" autocomplete="off" id="update-user-form">
                            @csrf
                            <input type="hidden" name="uid">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required" for="name">User Name</label>
                                        <input type="text" class="form-control" name="name" id="name" placeholder="Enter name">
                                        <span class="text-danger error-text name_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required" for="email">Email</label>
                                        <input type="email" class="form-control" name="email" id="email" placeholder="Enter email">
                                        <span class="text-danger error-text email_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required" for="district_id">District</label>
                                        <select class="form-control" name="district_id" id="district_id">
                                            <option value="">Select District</option>
                                            @foreach ($districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger error-text district_id_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required" for="role_name">Roles</label>
                                        <select class="form-control" name="role_name[]" id="role_name1" multiple>
                                            <option value="">Select Role (Multiple)</option>
                                            @foreach ($roles as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger error-text role_name_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required" for="pngo_id">PNGO</label>
                                        <select class="form-control" name="pngo_id" id="pngo_id">
                                            <option value="">Select PNGO</option>
                                            @foreach ($pngos as $pngo)
                                            <option value="{{ $pngo->id }}">{{ $pngo->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger error-text pngo_id_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required" for="status">Status</label>
                                        <select class="form-control" name="status" id="status">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                        <span class="text-danger error-text status_error"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>
</section>






<div class="modal fade" id="permissionsViewModal" tabindex="-1" role="dialog"
    aria-labelledby="permissionsViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permissionsViewModalLabel">Role Permissions</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Dynamic content will be loaded here (view permissions) -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- User Permissions Edit Modal -->
<div class="modal fade" id="userPermissionsModal" tabindex="-1" aria-labelledby="userPermissionsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userPermissionsModalLabel">Edit User Permissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="editUserPermissionsContent">
                    <!-- Dynamic Content Loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="saveUserPermissions" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>





@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.2.0/dist/sweetalert2.min.js"></script>
<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        // Clear error messages on modal close
        $('#addUserModal').on('hidden.bs.modal', function() {
            $('#add-user-form').find('span.error-text').text('');
        });

        // When the 'View Permissions' button is clicked
        $('.view-permissions').on('click', function() {
            var userId = $(this).data('id'); // Get the user ID from the button's data attribute
            $.ajax({
                url: '/mne/users/' + userId +
                    '/permissions', // Make the AJAX request to fetch user permissions
                method: 'GET',
                success: function(response) {
                    var permissionsList =
                        ''; // Initialize an empty string for the permissions

                    // Loop through the grouped permissions returned in the response
                    $.each(response.permissions, function(category, permissions) {
                        permissionsList +=
                            '<div class="category-box">'; // Start of category box
                        permissionsList += '<div class="category-header">' +
                            category + '</div>'; // Category name header
                        permissionsList +=
                            '<div class="permissions-grid">'; // Start of permission grid

                        permissions.forEach(function(permission) {
                            permissionsList +=
                                '<div class="permission-item">' + permission
                                .name + '</div>'; // Display permission name
                        });

                        permissionsList +=
                            '</div></div>'; // Close permissions grid and category box
                    });

                    // Inject the generated HTML into the modal body
                    $('#permissionsViewModal .modal-body').html(permissionsList);

                    // Show the modal
                    $('#permissionsViewModal').modal('show');
                },
                error: function() {
                    alert('Error loading permissions.');
                }
            });
        });


        $('#add-user-form').on('submit', function(e) {
            e.preventDefault();

            // Disable the submit button to prevent double-clicking
            $(this).find(':submit').prop('disabled', true);

            // Show the loader overlay (if any)
            $('#loader-overlay').show();

            var form = this;

            $.ajax({
                url: $(form).attr('action'),
                method: $(form).attr('method'),
                data: new FormData(form),
                processData: false,
                dataType: 'json',
                contentType: false,
                beforeSend: function() {
                    // Clear previous error messages
                    $(form).find('span.error-text').text('');
                },
                success: function(data) {
                    if (data.code == 0) {
                        // Handle validation errors
                        $.each(data.error, function(prefix, val) {
                            // Find the error span by class name and set the error text
                            $(form).find('span.' + prefix + '_error').text(val[0]);
                        });

                        // Focus on the first error field
                        var firstErrorField = $(form).find('span.error-text').first().prev(
                            'input, select');
                        if (firstErrorField.length) {
                            firstErrorField.focus();
                        }
                    } else {
                        // Handle success response
                        var redirectUrl = data.redirect;
                        $('#addUserModal').modal('hide');
                        $('#addUserModal').find('form')[0].reset();

                        // Customize Swal design for success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.msg,
                            showConfirmButton: false,
                            timer: 1500,
                            background: '#eaf9e7', // Light green background
                            color: '#2e8b57', // Text color
                            confirmButtonColor: '#4CAF50' // Button color
                        });

                        setTimeout(function() {
                            window.location.href = redirectUrl;
                        }, 1000);
                    }
                },
                error: function(xhr, status, error) {
                    // Handle unexpected errors with toastr
                    toastr.error('Something went wrong! Please try again.');
                    console.log(xhr.responseText); // For debugging
                },
                complete: function() {
                    // Enable the submit button and hide the loader overlay
                    $(form).find(':submit').prop('disabled', false);
                    $('#loader-overlay').hide();
                }
            });
        });

        $(document).on('click', '#editUserBtn', function() {
            var user_id = $(this).data('id');
            $('.editUser').find('form')[0].reset();
            $('.editUser').find('span.error-text').text('');

            $.post("{{ route('getUserDetails') }}", {
                user_id: user_id
            }, function(data) {
                const modal = $('.editUser');

                modal.find('input[name="uid"]').val(data.details.id);
                modal.find('input[name="name"]').val(data.details.name);
                modal.find('input[name="email"]').val(data.details.email);
                modal.find('select[name="district_id"]').val(data.details.district_id);
                modal.find('select[name="pngo_id"]').val(data.details.pngo_id);
                modal.find('select[name="status"]').val(data.details.status);

                // ✅ Set Select2 roles
                let roleSelect = modal.find('select[name="role_name[]"]');
                roleSelect.val(data.details.role_name).trigger('change');

                modal.modal('show');
            }, 'json');
        });



        // Update Class RECORD
        $('#update-user-form').on('submit', function(e) {
            e.preventDefault();
            var form = this;

            // Disable the submit button to prevent double-clicking
            $(form).find(':submit').prop('disabled', true);

            // Show the loader overlay
            $('#loader-overlay').show();

            $.ajax({
                url: $(form).attr('action'),
                method: $(form).attr('method'),
                data: new FormData(form),
                processData: false,
                dataType: 'json',
                contentType: false,
                beforeSend: function() {
                    $(form).find('span.error-text').text('');
                },
                success: function(data) {
                    if (data.code == 0) {
                        // Show errors if any
                        $.each(data.error, function(prefix, val) {
                            $(form).find('span.' + prefix + '_error').text(val[0]);
                        });
                    } else {
                        // Hide modal and reset form
                        $('.editUser').modal('hide');
                        $('.editUser').find('form')[0].reset();

                        // Success message using SweetAlert2
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.msg,
                            timer: 2000, // Adjust the duration as needed
                            showConfirmButton: false,
                        });

                        // Redirect after a delay (if provided)
                        var redirectUrl = data.redirect;
                        setTimeout(function() {
                            window.location.href = redirectUrl;
                        }, 2000); // Adjust the delay as needed (in milliseconds)
                    }
                },
                complete: function() {
                    // Enable the submit button and hide the loader overlay
                    $(form).find(':submit').prop('disabled', false);
                    $('#loader-overlay').hide();
                },
                error: function(xhr, status, error) {
                    // Show error notification using SweetAlert2 if the AJAX request fails
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong. Please try again.',
                        showConfirmButton: true,
                    });

                    // Optionally, log the error to the console
                    console.error('Error:', status, error);
                }
            });
        });


        $('.edit-user-permissions').on('click', function() {
            var userId = $(this).data('id');
            var userName = $(this).data('name');

            $('#userPermissionsModalLabel').text('Edit Permissions for ' + userName);

            $.ajax({
                url: '/mne/users/' + userId + '/edit-permissions',
                method: 'GET',
                success: function(response) {
                    if (response && response.user && response.allPermissions) {
                        var editForm = '<form id="editUserPermissionsForm">';
                        editForm += '<input type="hidden" name="user_id" value="' + userId +
                            '">';

                        var groupedPermissions = {};

                        // Group permissions by category
                        $.each(response.allPermissions, function(index, permission) {
                            var category = permission.category || 'Uncategorized';
                            if (!groupedPermissions[category]) {
                                groupedPermissions[category] = [];
                            }
                            groupedPermissions[category].push(permission);
                        });

                        // Render grouped permissions
                        $.each(groupedPermissions, function(category, permissions) {
                            editForm += '<div class="mb-4">';
                            editForm +=
                                '<h5 class="text-danger classic-category-title">' +
                                category + '</h5>';
                            editForm +=
                                '<div class="classic-row">'; // Apply the grid layout

                            $.each(permissions, function(index, permission) {
                                var checked = response.userPermissions[
                                        category] &&
                                    response.userPermissions[category].some(
                                        p => p.id === permission.id) ?
                                    'checked' : '';

                                editForm += `
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input classic-checkbox" type="checkbox" name="permissions[]" value="${permission.id}" ${checked} id="permission-${permission.id}">
                                    <label for="permission-${permission.id}" class="classic-checkbox-label">${permission.name}</label>
                                </div>
                            </div>`;
                            });

                            editForm +=
                                '</div></div>'; // Close the row and category div
                        });

                        editForm += '</form>';
                        $('#editUserPermissionsContent').html(editForm);
                        $('#userPermissionsModal').modal('show');
                    } else {
                        alert('Error loading permissions.');
                    }
                },
                error: function() {
                    alert('Error fetching user permissions.');
                }
            });
        });


        // Save Permissions
        $('#saveUserPermissions').on('click', function() {
            var formData = $('#editUserPermissionsForm').serialize();

            $.ajax({
                url: '/mne/users/' + $('input[name="user_id"]').val() + '/update-permissions',
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    Swal.fire({
                        title: 'Permissions Updated!',
                        text: 'User permissions have been successfully updated.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#userPermissionsModal').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    alert('Error updating permissions: ' + error);
                }
            });
        });




    });
</script>

<script>
    $(document).ready(function() {
        $('#role_name1').select2({
            dropdownParent: $('#editUserModal'),
            placeholder: "Select Role(s)",
            width: '100%'
        });

        $('#role_name').select2({
            placeholder: "Select Role(s)",
            width: '100%',
            dropdownParent: $('#addUserModal') // Adjust modal ID if necessary
        });

    });
</script>

@endpush