@extends('dashboard.layouts.admin-layout')

@section('title', 'Pngo Management')

@push('styles')
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
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="container">
                    <h2>Roles and Permissions</h2>

                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Role Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr>
                                    <td>{{ $role->name }}</td>
                                    <td>
                                        <!-- View Permissions Button -->
                                        <button class="btn btn-info btn-sm view-permissions" data-id="{{ $role->id }}"
                                            data-toggle="modal" data-target="#permissionsViewModal">View</button>

                                        <!-- Edit Permissions Button -->
                                        <button class="btn btn-warning btn-sm edit-permissions" data-id="{{ $role->id }}"
                                            data-toggle="modal" data-target="#permissionsEditModal">Edit</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- View Permissions Modal -->
    <div class="modal fade" id="permissionsViewModal" tabindex="-1" role="dialog"
        aria-labelledby="permissionsViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="permissionsViewModalLabel">Role Permissions</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Dynamic content will be loaded here (view permissions) -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Permissions Modal -->
    <div class="modal fade" id="permissionsEditModal" tabindex="-1" aria-labelledby="permissionsEditModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Edit Role Permissions</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="editPermissionsContent">
                        <!-- Permissions form will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="savePermissions">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // When View button is clicked
            $('.view-permissions').on('click', function() {
                var roleId = $(this).data('id'); // Get role ID
                $.ajax({
                    url: '/mne/role/' + roleId + '/permissions', // Fetch permissions for viewing
                    method: 'GET',
                    success: function(response) {
                        var permissionsList = '';

                        // Loop through grouped permissions
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
                                    .name + '</div>';
                            });

                            permissionsList +=
                                '</div></div>'; // Close permissions grid and category box
                        });

                        // Display in modal
                        $('#permissionsViewModal .modal-body').html(permissionsList);
                    },
                    error: function() {
                        alert('Error loading permissions.');
                    }
                });
            });

            $('.edit-permissions').on('click', function() {
                var roleId = $(this).data('id');
                var roleName = $(this).data('name');

                $('#modalTitle').text('Edit Permissions for ' + roleName);

                $.ajax({
                    url: '/mne/role/' + roleId + '/edit-permissions',
                    method: 'GET',
                    success: function(response) {
                        if (response && response.role && response.allPermissions) {
                            var editForm = '<form id="editPermissionsForm">';
                            editForm += '<input type="hidden" name="role_id" value="' + roleId +
                                '">';

                            // Group permissions by category
                            var groupedPermissions = {};

                            // Iterate over all permissions
                            $.each(response.allPermissions, function(index, permission) {
                                var category = permission.category || 'Uncategorized';
                                if (!groupedPermissions[category]) {
                                    groupedPermissions[category] = [];
                                }
                                groupedPermissions[category].push(permission);
                            });

                            // Render permissions grouped by category
                            $.each(groupedPermissions, function(category, permissions) {
                                editForm += '<div class="mb-4">';
                                editForm +=
                                    '<h5 class="text-danger classic-category-title">' +
                                    category + '</h5>';
                                editForm +=
                                    '<div class="classic-row">'; // Apply the grid layout

                                $.each(permissions, function(index, permission) {
                                    var checked = response.rolePermissions[
                                            category] &&
                                        response.rolePermissions[category].some(
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
                            $('#editPermissionsContent').html(editForm);
                            $('#permissionsEditModal').modal('show');
                        } else {
                            alert('Error loading permissions.');
                        }
                    },
                    error: function() {
                        alert('Error fetching permissions.');
                    }
                });
            });


            // Save permissions
            $('#savePermissions').on('click', function() {
                var formData = $('#editPermissionsForm').serialize();

                $.ajax({
                    url: '/mne/role/update-permissions/' + $('input[name="role_id"]').val(),
                    method: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        // Show SweetAlert after success
                        Swal.fire({
                            title: 'Permissions Updated!',
                            text: 'The permissions have been successfully updated.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            $('#permissionsEditModal').modal(
                                'hide'); // Hide the modal after saving
                            location
                                .reload(); // Optionally, reload the page to reflect changes
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        alert('Error updating permissions: ' + error);
                    }
                });
            });

            // To handle the modal closing properly after saving
            $('#permissionsEditModal').on('hidden.bs.modal', function() {
                // Reset the content of the modal to avoid stale data on reopening
                $('.modal-backdrop').remove();
                $('#editPermissionsContent').html('');
            });
        });
    </script>
@endpush
