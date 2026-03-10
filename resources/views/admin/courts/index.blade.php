@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded" style="background:#00284d;color:#fff;">
        <h4 class="mb-0">{{ __('messages.courts') }}</h4>
        <button class="btn btn-gold btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addCourtModal">
            <i class="bi bi-plus-circle"></i> {{ __('messages.add_court') }}
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" id="courtSearch" class="form-control" placeholder="Search by name/code...">
                </div>
                <div class="col-md-3">
                    <select id="statusFilter" class="form-select">
                        <option value="all">All Status</option>
                        <option value="active">{{ __('messages.active') }}</option>
                        <option value="inactive">{{ __('messages.inactive') }}</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="table-light">
                            <th width="60">#</th>
                            <th>{{ __('messages.court_name_en') }}</th>
                            <th>{{ __('messages.court_name_bn') }}</th>
                            <th>{{ __('messages.court_code') }}</th>
                            <th width="120">{{ __('messages.status') }}</th>
                            <th width="180">{{ __('messages.action') }}</th>
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
                                <td class="fw-semibold">{{ $court->name_en }}</td>
                                <td>{{ $court->name_bn ?? '-' }}</td>
                                <td>{{ $court->code }}</td>
                                <td>
                                    <span class="badge {{ $court->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $court->is_active ? __('messages.active') : __('messages.inactive') }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm me-1 edit-btn">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-btn">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No court found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCourtModal">
    <div class="modal-dialog">
        <form id="addCourtForm">
            @csrf
            <div class="modal-content p-3 border-0">
                <div class="modal-header border-0">
                    <h5>{{ __('messages.add_court') }}</h5>
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
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-gold px-4">{{ __('messages.save') }}</button>
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
            <div class="modal-content p-3 border-0">
                <div class="modal-header border-0">
                    <h5>{{ __('messages.edit_court') }}</h5>
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
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-gold px-4">{{ __('messages.update') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('css')
<style>
    .btn-gold { background:#d4a017; color:#fff; border-color:#d4a017; }
    .btn-gold:hover { background:#b38b0f; color:#fff; border-color:#b38b0f; }
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
