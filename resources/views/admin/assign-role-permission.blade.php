@extends('dashboard.layouts.admin-layout')

@section('title', 'Role and Permission Management')

@section('content')
<div class="container">
    <h2>Roles and Permissions</h2>

    <table class="table">
        <thead>
            <tr>
                <th>Role Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
            <tr>
                <td>{{ $role->name }}</td>
                <td>
                    <!-- View Permissions Button -->
                    <button class="btn btn-info view-permissions" data-id="{{ $role->id }}" data-toggle="modal" data-target="#permissionsModal">View</button>
                    
                    <!-- Edit Permissions Button -->
                    <button class="btn btn-warning edit-permissions" data-id="{{ $role->id }}" data-toggle="modal" data-target="#permissionsModal">Edit</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Permissions Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1" role="dialog" aria-labelledby="permissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permissionsModalLabel">Role Permissions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Dynamic content will be loaded here (either view or edit form) -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="saveChangesBtn" class="btn btn-primary" style="display:none">Save Changes</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        // When View button is clicked
        $('.view-permissions').on('click', function () {
            var roleId = $(this).data('id'); // Get role ID
            $.ajax({
                url: '/mne/role/' + roleId + '/permissions', // Fetch permissions for viewing
                method: 'GET',
                success: function (response) {
                    var permissionsList = '<ul>';
                    response.permissions.forEach(function (permission) {
                        permissionsList += '<li>' + permission.name + ' (' + permission.category + ')</li>';
                    });
                    permissionsList += '</ul>';

                    // Display in modal
                    $('#permissionsModal .modal-body').html(permissionsList);
                    $('#saveChangesBtn').hide(); // Hide Save Changes button for view
                },
                error: function () {
                    alert('Error loading permissions.');
                }
            });
        });

        // When Edit button is clicked
        $('.edit-permissions').on('click', function () {
            var roleId = $(this).data('id'); // Get role ID
            $.ajax({
                url: '/mne/role/' + roleId + '/edit-permissions', // Fetch permissions for editing
                method: 'GET',
                success: function (response) {
                    var editForm = '<h4>Edit Permissions for Role</h4>';
                    response.allPermissions.forEach(function (permission) {
                        var checked = response.rolePermissions.includes(permission.id) ? 'checked' : '';
                        editForm += '<div class="form-check">' +
                                    '<input class="form-check-input" type="checkbox" name="permissions[]" value="' + permission.id + '" ' + checked + '>' +
                                    '<label class="form-check-label" for="permission-' + permission.id + '">' + permission.name + '</label>' +
                                    '</div>';
                    });

                    // Display in modal
                    $('#permissionsModal .modal-body').html(editForm);
                    $('#saveChangesBtn').show(); // Show Save Changes button for edit
                },
                error: function () {
                    alert('Error loading permissions.');
                }
            });
        });

        // Handle Save Changes
        $('#saveChangesBtn').on('click', function () {
            var permissions = [];
            $('input[name="permissions[]"]:checked').each(function () {
                permissions.push($(this).val());
            });

            var roleId = $('.edit-permissions').data('id'); // Get role ID for save

            $.ajax({
                url: '/mne/role/' + roleId + '/update-permissions', // Send updated permissions
                method: 'POST',
                data: {
                    permissions: permissions,
                    _token: '{{ csrf_token() }}'
                },
                success: function () {
                    alert('Permissions updated successfully.');
                    location.reload(); // Reload the page
                },
                error: function () {
                    alert('Error saving permissions.');
                }
            });
        });
    });
</script>
@endsection
