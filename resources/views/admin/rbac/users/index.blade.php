@extends('admin.layouts.adminlayout')
@section('title', 'Users List')

@section('content')
<div class="container py-2">

    <!-- Header -->
    <div class="lawyer-card d-flex justify-content-between align-items-center mb-3 p-2" style="background:#00284d; color:#fff;">
        <h4 class="mb-0 profile-section-title text-white">Users</h4>
        @can('Create Users')
        <a href="{{ route('admin.users.create') }}" class="btn btn-gold btn-sm px-4">
            <i class="bi bi-plus-lg"></i> Create User
        </a>
        @endcan
    </div>

    <!-- Users Grid -->
    <div class="lawyer-card p-3">
        <div class="row g-4">
            @foreach($users as $user)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0 user-card h-100 position-relative">
                    <!-- Status Accent Bar -->
                    <div class="status-bar position-absolute w-100" style="height:6px; background: {{ $user->is_active ? '#28a745' : '#dc3545' }}; top:0; left:0; border-top-left-radius:0.8rem; border-top-right-radius:0.8rem;"></div>

                    <div class="card-body d-flex flex-column justify-content-between">
                        <!-- User Info -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-1">{{ $user->name }}</h5>
                                <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <p class="text-muted mb-1">{{ $user->email }}</p>
                            <p class="text-muted mb-1">{{ $user->phone_number ?? 'N/A' }}</p>
                            <p class="text-muted mb-2"><strong>User Type:</strong> {{ ucfirst($user->user_type) }}</p>

                            <!-- Roles -->
                            <div class="mb-2">
                                @foreach($user->roles as $role)
                                    <span class="badge bg-primary me-1 mb-1">{{ $role->name }}</span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            @can('View User Permissions')
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#permissionsModal-{{ $user->id }}">
                                    View Permissions
                                    <span class="badge bg-light text-dark">{{ $user->permissions->count() }}</span>
                                </button>
                            </div>
                            @endcan
                            
                            <div class="d-flex gap-2">
                                @can('Edit Users')
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                @endcan
                                @can('Delete Users')
                                <button type="button" class="btn btn-sm btn-outline-danger delete-user" data-id="{{ $user->id }}">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions Modal -->
                <div class="modal fade" id="permissionsModal-{{ $user->id }}" tabindex="-1" aria-labelledby="permissionsModalLabel-{{ $user->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="permissionsModalLabel-{{ $user->id }}">
                                    Permissions for {{ $user->name }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                @php
                                    $grouped = $user->permissions->groupBy(function($perm){
                                        return optional($perm->group)->name ?? 'Ungrouped';
                                    });
                                @endphp
                                @foreach($grouped as $groupName => $permissions)
                                    <div class="mb-3">
                                        <h6 class="text-primary">{{ $groupName }}</h6>
                                        @foreach($permissions as $permission)
                                            <span class="badge bg-secondary me-1 mb-1">{{ $permission->name }}</span>
                                        @endforeach
                                    </div>
                                    <hr>
                                @endforeach
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

@push('css')
<style>
.user-card {
    border-radius: 0.8rem;
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
}
.user-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 25px rgba(0,0,0,0.15);
}
.card-title {
    font-weight: 600;
    font-size: 1.1rem;
}
.badge {
    font-size: 0.8rem;
    padding: 0.35em 0.65em;
}
.status-bar {
    z-index: 1;
}
.btn-gold {
    background-color:#d4a017;
    color:#fff;
    border-radius:8px;
    font-weight:500;
}
.btn-gold:hover {
    background-color:#b38b0f;
}
</style>
@endpush

@push('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){

    // Delete user
    document.querySelectorAll('.delete-user').forEach(btn=>{
        btn.addEventListener('click', function(){
            const userId = this.dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "This user will be deleted permanently!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result)=>{
                if(result.isConfirmed){
                    fetch("{{ url('admin/users') }}/" + userId, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    }).then(res=>res.json())
                    .then(res=>{
                        if(res.success){
                            Swal.fire('Deleted!', res.success, 'success').then(()=> location.reload());
                        } else {
                            Swal.fire('Error', 'Something went wrong!', 'error');
                        }
                    }).catch(err=>{
                        Swal.fire('Error', 'Something went wrong!', 'error');
                        console.error(err);
                    });
                }
            });
        });
    });

});
</script>
@endpush
