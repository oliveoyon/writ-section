@extends('admin.layouts.adminlayout')

@section('content')
@php
    $activeCourtCount = $courts->where('is_active', true)->count();
    $inactiveCourtCount = $courts->count() - $activeCourtCount;
    $lockedCourtCount = $courts->filter(fn ($court) => $court->movements_exists || $court->dispatch_batches_exists)->count();
@endphp

<div class="container py-4 courts-page">
    <div class="page-heading mb-3">
        <div>
            <div class="system-mark">RTFTS Setup</div>
            <h4 class="mb-0">{{ __('messages.courts') }}</h4>
            <small>Manage court names used for file dispatch and court return records.</small>
        </div>
        <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#addCourtModal">
            <i class="bi bi-plus-circle"></i> Add
        </button>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="summary-box">
                <i class="bi bi-building"></i>
                <span>Total Courts</span>
                <strong>{{ $courts->count() }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-box active">
                <i class="bi bi-check-circle"></i>
                <span>Active Courts</span>
                <strong>{{ $activeCourtCount }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-box locked">
                <i class="bi bi-lock"></i>
                <span>History Locked</span>
                <strong>{{ $lockedCourtCount }}</strong>
            </div>
        </div>
    </div>

    <div class="admin-panel">
        <div class="panel-heading">
            <div>
                <h5>Court List</h5>
                <span>{{ $activeCourtCount }} active, {{ $inactiveCourtCount }} inactive</span>
            </div>
        </div>
        <div class="filter-bar">
            <div class="row g-2">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="courtSearch" class="form-control" placeholder="Search name or code">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="statusFilter" class="form-select form-select-sm">
                        <option value="all">All Status</option>
                        <option value="active">{{ __('messages.active') }}</option>
                        <option value="inactive">{{ __('messages.inactive') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle modern-table mb-0">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>{{ __('messages.court_name_en') }}</th>
                        <th>{{ __('messages.court_name_bn') }}</th>
                        <th width="120">{{ __('messages.court_code') }}</th>
                        <th width="110">{{ __('messages.status') }}</th>
                        <th width="120">Usage</th>
                        <th width="130" class="text-end">{{ __('messages.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($courts as $index => $court)
                        <tr id="row-{{ $court->id }}"
                            class="court-row"
                            data-name-en="{{ strtolower($court->name_en) }}"
                            data-name-bn="{{ strtolower((string) $court->name_bn) }}"
                            data-code="{{ strtolower($court->code) }}"
                            data-status="{{ $court->is_active ? 'active' : 'inactive' }}">
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-bold text-dark">{{ $court->name_en }}</td>
                            <td>{{ $court->name_bn ?? '-' }}</td>
                            <td><code>{{ $court->code }}</code></td>
                            <td>
                                <span class="status-badge {{ $court->is_active ? 'active' : 'inactive' }}">
                                    {{ $court->is_active ? __('messages.active') : __('messages.inactive') }}
                                </span>
                            </td>
                            <td>
                                @if($court->movements_exists || $court->dispatch_batches_exists)
                                    <span class="usage-badge locked">Locked</span>
                                @else
                                    <span class="usage-badge">Unused</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-action edit-btn" title="Edit court">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                @unless($court->movements_exists || $court->dispatch_batches_exists)
                                    <button class="btn btn-action danger delete-btn" title="Delete unused court">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No court found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addCourtModal">
    <div class="modal-dialog">
        <form id="addCourtForm">
            @csrf
            <div class="modal-content modal-panel">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.add_court') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ __('messages.court_name_en') }}</label>
                        <input type="text" class="form-control" name="name_en" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('messages.court_name_bn') }}</label>
                        <input type="text" class="form-control" name="name_bn">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('messages.court_code') }}</label>
                        <input type="text" class="form-control" name="code" required>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                        <label class="form-check-label">{{ __('messages.active') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-gold">{{ __('messages.save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editCourtModal">
    <div class="modal-dialog">
        <form id="editCourtForm">
            @csrf
            <input type="hidden" id="edit_id">
            <div class="modal-content modal-panel">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.edit_court') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ __('messages.court_name_en') }}</label>
                        <input type="text" class="form-control" id="edit_name_en" name="name_en" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('messages.court_name_bn') }}</label>
                        <input type="text" class="form-control" id="edit_name_bn" name="name_bn">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('messages.court_code') }}</label>
                        <input type="text" class="form-control" id="edit_code" name="code" required>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label">{{ __('messages.active') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-gold">{{ __('messages.update') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('css')
<style>
    .courts-page { max-width: 1120px; }
    .page-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        background: #fff;
        border: 1px solid #e3e8ef;
        border-top: 3px solid #00284d;
        border-bottom-color: #d4a017;
        border-radius: 4px;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .08);
    }
    .page-heading h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
    .page-heading small { color: #6b7280; font-weight: 600; }
    .system-mark { color: #b87d08; font-size: .82rem; font-weight: 800; }
    .summary-box {
        position: relative;
        min-height: 94px;
        padding: .85rem .9rem;
        background: #fff;
        border: 1px solid #e3e8ef;
        border-left: 4px solid #00284d;
        border-radius: 4px;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .06);
    }
    .summary-box.active { border-left-color: #21854a; }
    .summary-box.locked { border-left-color: #d4a017; }
    .summary-box i { position: absolute; right: .85rem; top: .85rem; color: #d4a017; font-size: 1.35rem; }
    .summary-box span { display: block; color: #6b7280; font-size: .82rem; font-weight: 800; text-transform: uppercase; }
    .summary-box strong { display: block; color: #111827; font-size: 2rem; line-height: 1.1; margin-top: .45rem; }
    .admin-panel {
        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: 4px;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .07);
        overflow: hidden;
    }
    .panel-heading {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: .75rem 1rem;
        background: #fbfcfe;
        border-top: 3px solid #00284d;
        border-bottom: 1px solid #e5e7eb;
    }
    .panel-heading h5 { margin: 0; color: #1f2937; font-size: 1rem; font-weight: 800; }
    .panel-heading span { color: #6b7280; font-size: .84rem; font-weight: 600; }
    .filter-bar { padding: .85rem 1rem; border-bottom: 1px solid #eef2f7; background: #fff; }
    .filter-bar .input-group-text { background: #f2f7fc; color: #0b4f8a; border-radius: 4px 0 0 4px; }
    .filter-bar .form-control,
    .filter-bar .form-select { border-radius: 4px; }
    .filter-bar .form-control:focus,
    .filter-bar .form-select:focus,
    .modal-panel .form-control:focus { border-color: #d4a017; box-shadow: 0 0 0 .15rem rgba(212, 160, 23, .15); }
    table.modern-table th,
    table.modern-table td { padding: .72rem .9rem; vertical-align: middle; }
    table.modern-table thead th {
        background: #eef5fb;
        color: #00284d;
        border-bottom: 0;
        font-weight: 800;
        white-space: nowrap;
    }
    table.modern-table tbody tr:hover { background: #fffaf0; }
    table.modern-table code {
        color: #0b4f8a;
        background: #f2f7fc;
        border: 1px solid #d8e3ef;
        border-radius: 4px;
        padding: .18rem .4rem;
        font-size: .82rem;
    }
    .status-badge,
    .usage-badge {
        display: inline-flex;
        border-radius: 4px;
        padding: .2rem .5rem;
        font-size: .76rem;
        font-weight: 800;
        border: 1px solid #d8e3ef;
        background: #f7fbff;
        color: #0b4f8a;
    }
    .status-badge.active { background: #eefbf3; border-color: #bce8ca; color: #186a36; }
    .status-badge.inactive { background: #fff5f5; border-color: #f1b7b7; color: #a93b2d; }
    .usage-badge.locked { background: #fff7e6; border-color: #f3d58e; color: #805500; }
    .btn-gold {
        background-color: #d4a017;
        color: #111827;
        border: 1px solid #c89313;
        border-radius: 4px;
        font-weight: 800;
    }
    .btn-gold:hover { background-color: #b38b0f; color: #fff; border-color: #b38b0f; }
    .btn-action {
        width: 2rem;
        height: 2rem;
        display: inline-grid;
        place-items: center;
        padding: 0;
        border-radius: 4px;
        border: 1px solid #bcd0e2;
        color: #0b4f8a;
        background: #f2f7fc;
    }
    .btn-action:hover { background: #0b4f8a; color: #fff; }
    .btn-action.danger { border-color: #f1b7b7; color: #a93b2d; background: #fff5f5; }
    .btn-action.danger:hover { background: #a93b2d; color: #fff; }
    .modal-panel { border: 0; border-radius: 4px; overflow: hidden; }
    .modal-panel .modal-header {
        background: #00284d;
        color: #fff;
        border-bottom: 3px solid #d4a017;
    }
    .modal-panel .modal-title { color: #fff; font-weight: 800; }
    .modal-panel .modal-body,
    .modal-panel .modal-footer { padding: 1rem; }
    .modal-panel .form-control { border-radius: 4px; }
    @media (max-width: 768px) {
        .page-heading { align-items: stretch; flex-direction: column; }
        .page-heading .btn { width: 100%; }
        table.modern-table { min-width: 820px; }
    }
</style>
@endpush

@push('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const search = document.getElementById('courtSearch');
    const statusFilter = document.getElementById('statusFilter');

    function applyTableFilter() {
        const keyword = (search?.value || '').trim().toLowerCase();
        const status = (statusFilter?.value || 'all').toLowerCase();

        document.querySelectorAll('.court-row').forEach(row => {
            const nameEn = row.getAttribute('data-name-en') || '';
            const nameBn = row.getAttribute('data-name-bn') || '';
            const code = row.getAttribute('data-code') || '';
            const rowStatus = row.getAttribute('data-status') || '';

            const matchKeyword = keyword === '' || nameEn.includes(keyword) || nameBn.includes(keyword) || code.includes(keyword);
            const matchStatus = status === 'all' || rowStatus === status;

            row.style.display = (matchKeyword && matchStatus) ? '' : 'none';
        });
    }

    search?.addEventListener('input', applyTableFilter);
    statusFilter?.addEventListener('change', applyTableFilter);

    document.getElementById('addCourtForm').addEventListener('submit', function (e) {
        e.preventDefault();
        fetch("{{ route('admin.courts.store') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body: new FormData(this)
        }).then(r => r.json()).then(data => {
            if (data.success) {
                Swal.fire('Success', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', 'Unable to save court', 'error');
            }
        }).catch(() => Swal.fire('Error', 'Unable to save court', 'error'));
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.closest('tr').id.split('-')[1];
            fetch(`/admin/courts/${id}/edit`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_name_en').value = data.name_en || '';
                    document.getElementById('edit_name_bn').value = data.name_bn || '';
                    document.getElementById('edit_code').value = data.code || '';
                    document.getElementById('edit_is_active').checked = !!data.is_active;
                    new bootstrap.Modal(document.getElementById('editCourtModal')).show();
                });
        });
    });

    document.getElementById('editCourtForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('edit_id').value;
        const payload = new FormData(this);
        payload.append('_method', 'PUT');
        fetch(`/admin/courts/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body: payload
        }).then(r => r.json()).then(data => {
            if (data.success) {
                Swal.fire('Updated', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', 'Unable to update court', 'error');
            }
        }).catch(() => Swal.fire('Error', 'Unable to update court', 'error'));
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.closest('tr').id.split('-')[1];
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#d33'
            }).then(result => {
                if (!result.isConfirmed) return;
                const payload = new FormData();
                payload.append('_method', 'DELETE');
                fetch(`/admin/courts/${id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf },
                    body: payload
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        Swal.fire('Deleted', data.message, 'success');
                        document.getElementById(`row-${id}`)?.remove();
                    } else {
                        Swal.fire('Blocked', data.message || 'Unable to delete court', 'warning');
                    }
                }).catch(() => Swal.fire('Error', 'Unable to delete court', 'error'));
            });
        });
    });
});
</script>
@endpush
