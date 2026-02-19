@extends('admin.layouts.adminlayout')
@section('title', 'Users List')

@section('content')
<div class="container py-2">

    <!-- Header -->
    <div class="user-card d-flex justify-content-between align-items-center mb-3 p-2"
        style="background:#00284d; color:#fff;">
        <h4 class="mb-0 profile-section-title text-white">Users</h4>
        @can('Create Users')
            <a href="{{ route('admin.users.create') }}" class="btn btn-gold btn-sm px-4">
                <i class="bi bi-plus-lg"></i> Create User
            </a>
        @endcan
    </div>

    <div class="user-card p-3">

        <!-- 🔍 FILTER ROW -->
        <div class="row mb-3 g-2">
            <div class="col-md-4">
                <input type="text" id="nameSearch" class="form-control"
                    placeholder="🔍 Search by name...">
            </div>

            <div class="col-md-3">
                <select id="userTypeFilter" class="form-select">
                    <option value="all">All User Types</option>
                </select>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-responsive">
            <table class="table table-hover align-middle user-table">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th class="d-none d-md-table-cell">Contact</th>
                        <th>User Type</th>
                        <th>Status</th>
                        <th class="d-none d-lg-table-cell">Roles</th>
                        <th width="190" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($users as $user)
                    <tr class="user-item"
                        data-user-type="{{ strtolower($user->user_type) }}"
                        data-name="{{ strtolower($user->name) }}">

                        <td>
                            <div class="fw-semibold">{{ $user->name }}</div>
                            <div class="small text-muted d-md-none">{{ $user->email }}</div>
                            <div class="small text-muted d-md-none">{{ $user->phone_number ?? 'N/A' }}</div>
                        </td>

                        <td class="d-none d-md-table-cell">
                            <div>{{ $user->email }}</div>
                            <div class="small text-muted">{{ $user->phone_number ?? 'N/A' }}</div>
                        </td>

                        <td>
                            <span class="badge bg-info text-dark px-3 py-2">
                                {{ ucfirst($user->user_type) }}
                            </span>
                        </td>

                        <td>
                            <span class="badge px-3 py-2 {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <td class="d-none d-lg-table-cell">
                            @foreach ($user->roles as $role)
                                <span class="badge bg-primary me-1 mb-1">{{ $role->name }}</span>
                            @endforeach
                        </td>

                        <td>
                            <div class="action-btns">

                                @can('View User Permissions')
                                <button type="button"
                                    class="btn btn-sm btn-permission"
                                    data-bs-toggle="modal"
                                    data-bs-target="#permissionsModal-{{ $user->id }}">
                                    <i class="bi bi-shield-lock"></i>
                                    <span class="d-none d-xl-inline">Permissions</span>
                                    <span class="badge bg-light text-dark ms-1">
                                        {{ $user->permissions->count() }}
                                    </span>
                                </button>
                                @endcan

                                @can('Edit Users')
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                   class="btn btn-sm btn-edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                @endcan

                                @can('Delete Users')
                                <button type="button"
                                    class="btn btn-sm btn-delete delete-user"
                                    data-id="{{ $user->id }}">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                                @endcan

                            </div>
                        </td>
                    </tr>

                    <!-- Permissions Modal -->
                    <div class="modal fade" id="permissionsModal-{{ $user->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Permissions for {{ $user->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    @php
                                        $grouped = $user->permissions->groupBy(function ($perm) {
                                            return optional($perm->group)->name ?? 'Ungrouped';
                                        });
                                    @endphp
                                    @foreach ($grouped as $groupName => $permissions)
                                        <div class="mb-3">
                                            <h6 class="text-primary">{{ $groupName }}</h6>
                                            @foreach ($permissions as $permission)
                                                <span class="badge bg-secondary me-1 mb-1">
                                                    {{ $permission->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                        <hr>
                                    @endforeach
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @endforeach

                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection


@push('css')
<style>
.user-card{
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
    margin-bottom:20px;
    background:#fff;
}
.user-table th{font-weight:600;font-size:14px;}
.user-table td{padding:14px 10px;}

.action-btns{
    display:flex;
    gap:6px;
    flex-wrap:wrap;
    justify-content:center;
}
.btn-permission{background:#eef2ff;color:#3b5bdb;border:1px solid #dbe4ff;}
.btn-permission:hover{background:#3b5bdb;color:#fff;}
.btn-edit{background:#e7f5ff;color:#0b7285;border:1px solid #c5f6fa;}
.btn-edit:hover{background:#0b7285;color:#fff;}
.btn-delete{background:#fff5f5;color:#c92a2a;border:1px solid #ffc9c9;}
.btn-delete:hover{background:#c92a2a;color:#fff;}

.btn-gold{background:#d4a017;color:#fff;border-radius:8px;font-weight:500;}
.btn-gold:hover{background:#b38b0f;}
</style>
@endpush


@push('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){

    // delete user (unchanged)
    document.querySelectorAll('.delete-user').forEach(btn=>{
        btn.addEventListener('click', function(){
            const userId=this.dataset.id;
            Swal.fire({
                title:'Are you sure?',
                text:"This user will be deleted permanently!",
                icon:'warning',
                showCancelButton:true,
                confirmButtonColor:'#d33',
                cancelButtonColor:'#3085d6',
                confirmButtonText:'Yes, delete it!'
            }).then((result)=>{
                if(result.isConfirmed){
                    fetch("{{ url('admin/users') }}/"+userId,{
                        method:'DELETE',
                        headers:{
                            'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,
                            'Accept':'application/json'
                        }
                    }).then(res=>res.json())
                    .then(res=>{
                        if(res.success){
                            Swal.fire('Deleted!',res.success,'success')
                            .then(()=>location.reload());
                        }else{
                            Swal.fire('Error','Something went wrong!','error');
                        }
                    }).catch(()=>{
                        Swal.fire('Error','Something went wrong!','error');
                    });
                }
            });
        });
    });

    // AUTO TYPE DROPDOWN
    const filter=document.getElementById('userTypeFilter');
    const users=document.querySelectorAll('.user-item');
    const search=document.getElementById('nameSearch');
    let types=new Set();

    users.forEach(u=>{
        let t=u.getAttribute('data-user-type');
        if(t) types.add(t);
    });

    types.forEach(type=>{
        let opt=document.createElement('option');
        opt.value=type;
        opt.textContent=type.charAt(0).toUpperCase()+type.slice(1);
        filter.appendChild(opt);
    });

    // 🔥 COMBINED FILTER (name + type)
    function applyFilter(){
        const selectedType=filter.value.toLowerCase();
        const searchText=search.value.toLowerCase();

        users.forEach(user=>{
            const type=user.getAttribute('data-user-type');
            const name=user.getAttribute('data-name');

            const matchType=(selectedType==='all' || type===selectedType);
            const matchName=(name.includes(searchText));

            if(matchType && matchName){
                user.style.display='';
            }else{
                user.style.display='none';
            }
        });
    }

    filter.addEventListener('change',applyFilter);
    search.addEventListener('keyup',applyFilter);

});
</script>
@endpush
