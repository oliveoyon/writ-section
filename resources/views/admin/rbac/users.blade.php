@extends('admin.layouts.admin-layout')
@section('title','User Management')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.css">
<style>
.card-user { transition: transform .2s; }
.card-user:hover { transform: scale(1.02); }
.badge-role { background-color: #0d6efd; color:#fff; margin:2px;}
.badge-read { background-color:#28a745;color:#fff;margin:2px;}
.badge-write { background-color:#007bff;color:#fff;margin:2px;}
.badge-delete { background-color:#dc3545;color:#fff;margin:2px;}
.offcanvas-body { max-height:80vh; overflow-y:auto; }
</style>
@endpush

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>User Management</h3>
        <button class="btn btn-success" data-bs-toggle="offcanvas" data-bs-target="#addUserCanvas">+ Add User</button>
    </div>

    <div class="row" id="userCards">
        @foreach($users as $user)
        <div class="col-md-4">
            <div class="card card-user shadow-sm mb-3">
                <div class="card-body">
                    <h5>{{ $user->name }}</h5>
                    <h6 class="text-muted">{{ $user->email }}</h6>
                    <div class="mt-2">
                        <strong>Roles:</strong>
                        @foreach($user->roles as $role)
                            <span class="badge badge-role">{{ $role->name }}</span>
                        @endforeach
                    </div>
                    <div class="mt-2">
                        <strong>Permissions:</strong>
                        @foreach($user->permissions as $perm)
                            <span class="badge badge-read">{{ $perm->name }}</span>
                        @endforeach
                    </div>

                    <div class="mt-3 d-flex justify-content-between">
                        <button class="btn btn-sm btn-outline-secondary btn-edit" data-id="{{ $user->id }}" data-bs-toggle="offcanvas" data-bs-target="#editUserCanvas{{ $user->id }}">Edit</button>
                        <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $user->id }}">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Offcanvas -->
        <div class="offcanvas offcanvas-end" id="editUserCanvas{{ $user->id }}">
            <div class="offcanvas-header">
                <h5>Edit User - {{ $user->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <form class="ajax-form" data-method="PUT" data-id="{{ $user->id }}">
                    @csrf
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control">
                    </div>

                    <h6>Roles</h6>
                    @foreach($roles as $role)
                        <div class="form-check">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="form-check-input" {{ $user->roles->contains('name',$role->name)?'checked':'' }}>
                            <label class="form-check-label">{{ $role->name }}</label>
                        </div>
                    @endforeach

                    <hr>
                    <h6>Permissions</h6>
                    @foreach($permissions as $perm)
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="form-check-input" {{ $user->permissions->contains('id',$perm->id)?'checked':'' }}>
                            <label class="form-check-label">{{ $perm->name }}</label>
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-success mt-3 w-100 btn-submit">Save Changes</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Add User Offcanvas -->
<div class="offcanvas offcanvas-end" id="addUserCanvas">
    <div class="offcanvas-header">
        <h5>Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form class="ajax-form" data-method="POST">
            @csrf
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <h6>Roles</h6>
            @foreach($roles as $role)
                <div class="form-check">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="form-check-input">
                    <label class="form-check-label">{{ $role->name }}</label>
                </div>
            @endforeach

            <hr>
            <h6>Permissions</h6>
            @foreach($permissions as $perm)
                <div class="form-check">
                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="form-check-input">
                    <label class="form-check-label">{{ $perm->name }}</label>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary mt-3 w-100 btn-submit">Save User</button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const token = document.querySelector('meta[name="csrf-token"]').content;

    function collectFormData(form){
        let data = {};
        form.querySelectorAll('input, select, textarea').forEach(input=>{
            if(input.type==='checkbox'){
                if(!data[input.name]) data[input.name] = [];
                if(input.checked) data[input.name].push(input.value);
            } else data[input.name] = input.value;
        });
        return data;
    }

    document.querySelectorAll('.ajax-form').forEach(form=>{
        form.addEventListener('submit', async function(e){
            e.preventDefault();
            const btn = form.querySelector('.btn-submit');
            btn.disabled = true;

            const method = form.dataset.method || 'POST';
            const id = form.dataset.id;
            const url = method==='POST'? '{{ route("admin.users.store") }}' : '{{ url("admin/users") }}/'+id;

            const data = collectFormData(form);
            try{
                const res = await fetch(url,{
                    method:method,
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token},
                    body: JSON.stringify(data)
                });
                const json = await res.json();
                if(json.success){
                    Swal.fire('Success', json.message,'success').then(()=> location.reload());
                } else {
                    let errs='';
                    if(json.errors){
                        for(const key in json.errors){
                            errs += json.errors[key].join(', ')+'<br>';
                        }
                    }
                    Swal.fire('Error', errs || json.message,'error');
                }
            } catch(err){
                Swal.fire('Error','Something went wrong','error');
            } finally{
                btn.disabled=false;
            }
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn=>{
        btn.addEventListener('click', function(){
            const id = btn.dataset.id;
            Swal.fire({
                title:'Are you sure?',
                text:'User will be deleted permanently!',
                icon:'warning',
                showCancelButton:true,
                confirmButtonText:'Yes, delete'
            }).then(async (result)=>{
                if(result.isConfirmed){
                    try{
                        const res = await fetch('{{ url("admin/users") }}/'+id,{
                            method:'DELETE',
                            headers:{'X-CSRF-TOKEN':token}
                        });
                        const json = await res.json();
                        if(json.success) Swal.fire('Deleted!', json.message,'success').then(()=> location.reload());
                        else Swal.fire('Error', json.message,'error');
                    }catch(err){
                        Swal.fire('Error','Something went wrong','error');
                    }
                }
            });
        });
    });
});
</script>
@endpush
