@extends('admin.layouts.adminlayout')
@section('title', 'Users')

@section('content')
<div class="container py-3 user-management-page">
    <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded user-page-header">
        <div>
            <h4 class="mb-0">User Management</h4>
            <small>Manage staff/admin users and lawyer accounts separately.</small>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.users.card-labels') }}" target="_blank" class="btn btn-light btn-sm fw-bold">
                <i class="bi bi-upc-scan" aria-hidden="true"></i> Print Cards
            </a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-warning btn-sm fw-bold">Create User</a>
        </div>
    </div>

    @if(session('swal-success'))
        <div class="alert alert-success">{{ session('swal-success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <ul class="nav nav-tabs user-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'users' ? 'active' : '' }}"
               href="{{ route('admin.users.index', array_filter(['tab' => 'users', 'staff_q' => $staffSearch])) }}">
                Users <span class="badge rounded-pill">{{ $staffUserCount }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'lawyers' ? 'active' : '' }}"
               href="{{ route('admin.users.index', array_filter(['tab' => 'lawyers', 'lawyer_q' => $lawyerSearch, 'lawyer_status' => $lawyerStatus])) }}">
                Lawyers <span class="badge rounded-pill">{{ $lawyerUserCount }}</span>
            </a>
        </li>
    </ul>

    @if($activeTab === 'users')
    <section class="user-panel">
        <div class="panel-heading">
            <div>
                <h5 class="mb-0">Users</h5>
                <small>{{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} of {{ $users->total() }}</small>
            </div>
            <form method="GET" action="{{ route('admin.users.index') }}" class="panel-search">
                <input type="hidden" name="tab" value="users">
                <input type="search" name="staff_q" class="form-control form-control-sm" value="{{ $staffSearch }}" placeholder="Name, employee ID, card">
                <button class="btn btn-sm btn-outline-brand" type="submit">Search</button>
                @if($staffSearch !== '')
                    <a class="btn btn-sm btn-light" href="{{ route('admin.users.index', ['tab' => 'users']) }}">Clear</a>
                @endif
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Employee ID</th>
                            <th>Card ID</th>
                            <th>Department</th>
                            <th>User Type</th>
                            <th>Status</th>
                            <th width="160">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->employee_id ?? '-' }}</td>
                                <td>{{ $user->login_id ?? '-' }}</td>
                                <td>{{ $user->departmentRelation?->label ?? '-' }}</td>
                                <td><span class="badge bg-info text-dark">{{ $userTypeLabels[$user->user_type] ?? ucfirst($user->user_type) }}</span></td>
                                <td>
                                    <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Deactivate this user? Their history will be preserved.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" {{ auth()->id() === $user->id ? 'disabled' : '' }}>Deactivate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </section>
    @endif

    @if($activeTab === 'lawyers')
    <section class="user-panel lawyer-panel">
        <div class="panel-heading">
            <div>
                <h5 class="mb-0">Lawyers</h5>
                <small>{{ $lawyerUsers->firstItem() ?? 0 }}-{{ $lawyerUsers->lastItem() ?? 0 }} of {{ $lawyerUsers->total() }}</small>
            </div>
            <form method="GET" action="{{ route('admin.users.index') }}" class="panel-search lawyer-search">
                <input type="hidden" name="tab" value="lawyers">
                <input type="search" name="lawyer_q" class="form-control form-control-sm" value="{{ $lawyerSearch }}" placeholder="Name, email, phone, SCB no.">
                <select name="lawyer_status" class="form-select form-select-sm">
                    <option value="" @selected($lawyerStatus === '')>All</option>
                    <option value="active" @selected($lawyerStatus === 'active')>Active</option>
                    <option value="inactive" @selected($lawyerStatus === 'inactive')>Inactive</option>
                </select>
                <button class="btn btn-sm btn-outline-brand" type="submit">Search</button>
                @if($lawyerSearch !== '' || $lawyerStatus !== '')
                    <a class="btn btn-sm btn-light" href="{{ route('admin.users.index', ['tab' => 'lawyers']) }}">Clear</a>
                @endif
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>SCB Membership No.</th>
                            <th>Status</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lawyerUsers as $user)
                            <tr>
                                <td>{{ $user->lawyer?->full_name ?? $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->lawyer?->phone ?? '-' }}</td>
                                <td>{{ $user->lawyer?->bar_council_id ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Deactivate this lawyer account? Their history will be preserved.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.users.activate', $user->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Activate this lawyer account?');">
                                            @csrf
                                            @method('PUT')
                                            <button class="btn btn-sm btn-outline-success" type="submit">Activate</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No lawyer accounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">
                {{ $lawyerUsers->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </section>
    @endif
</div>
@endsection

@push('css')
<style>
    .user-page-header { background:#00284d; color:#fff; }
    .user-page-header small { color: rgba(255,255,255,.8); }
    .header-actions { display: flex; flex-wrap: wrap; gap: .45rem; justify-content: flex-end; }
    .user-tabs { border-bottom-color: #d7dde5; gap: .35rem; }
    .user-tabs .nav-link { border: 1px solid #d7dde5; color: #374151; background: #f8fafc; font-weight: 700; padding: .65rem 1rem; }
    .user-tabs .nav-link.active { background: #00284d; border-color: #00284d; color: #fff; }
    .user-tabs .badge { background: rgba(0,40,77,.12); color: #00284d; margin-left: .35rem; }
    .user-tabs .nav-link.active .badge { background: #ffc107; color: #111827; }
    .user-panel { background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 1px 5px rgba(0,0,0,.04); }
    .panel-heading { display:flex; align-items:center; justify-content:space-between; gap: 1rem; padding: .9rem 1rem; border-bottom: 1px solid #e5e7eb; background: #f7f8fa; }
    .panel-heading h5 { color: #1f2937; font-weight: 700; font-size: 1rem; }
    .panel-heading small { color: #6b7280; font-weight: 600; }
    .panel-search { display:flex; align-items:center; gap:.45rem; flex-wrap:wrap; justify-content:flex-end; }
    .panel-search .form-control { width: 210px; }
    .lawyer-search .form-control { width: 250px; }
    .panel-search .form-select { width: 110px; }
    .btn-outline-brand { color:#00284d; border-color:#00284d; }
    .btn-outline-brand:hover { background:#00284d; border-color:#00284d; color:#fff; }
    .pagination-wrap { display:flex; justify-content:flex-end; padding-top: .9rem; }
    .pagination-wrap nav { margin-bottom: 0; }
    .pagination-wrap svg { width: 1rem; height: 1rem; }
    .lawyer-panel .panel-heading { border-left: 4px solid #d4a017; }
    .user-panel table { font-size: .92rem; }
    @media (max-width: 575.98px) {
        .summary-grid { grid-template-columns: 1fr; }
        .panel-heading { align-items: stretch; flex-direction: column; }
        .panel-search { justify-content: stretch; }
        .panel-search .form-control,
        .lawyer-search .form-control,
        .panel-search .form-select,
        .panel-search .btn { width: 100%; }
    }
</style>
@endpush
