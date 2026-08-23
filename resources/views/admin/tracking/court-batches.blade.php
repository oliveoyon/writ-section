@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 batch-page">
    <div class="batch-header mb-3">
        <div>
            <div class="system-mark">RTFTS Court</div>
            <h4 class="mb-0">Court Batches</h4>
            <small>Paginated dispatch and return batches</small>
        </div>
        <a href="{{ route('admin.tracking.court.dispatch.index') }}" class="btn btn-brand btn-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('tracking.receive.send_to_court') }}
        </a>
    </div>

    <form method="GET" class="batch-filters admin-panel mb-3">
        <div class="panel-heading">
            <div>
                <h5>Filter Batches</h5>
            <span>{{ $hasBatchSearch ? $batches->total() . ' result(s)' : 'Search required' }}</span>
            </div>
        </div>
        <div class="panel-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="q">Batch No or Case No</label>
                <input id="q" name="q" class="form-control" value="{{ request('q') }}" placeholder="DSP-... or WRPET 1/2026">
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="form-label" for="type">Type</label>
                <select id="type" name="type" class="form-select">
                    <option value="">All</option>
                    <option value="dispatch" @selected(request('type') === 'dispatch')>Dispatch</option>
                    <option value="return" @selected(request('type') === 'return')>Return</option>
                </select>
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label" for="court_id">{{ __('tracking.court.court') }}</label>
                <select id="court_id" name="court_id" class="form-select">
                    <option value="">All Courts</option>
                    @foreach($courts as $court)
                        <option value="{{ $court->id }}" @selected((string) request('court_id') === (string) $court->id)>{{ $court->displayName() }}</option>
                    @endforeach
                </select>
            </div>
            @if($canViewAll)
                <div class="col-sm-6 col-md-3">
                    <label class="form-label" for="creator_id">Created By</label>
                    <select id="creator_id" name="creator_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach($creators as $creator)
                            <option value="{{ $creator->id }}" @selected((string) request('creator_id') === (string) $creator->id)>{{ $creator->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-sm-6 col-md-3">
                <label class="form-label" for="date_from">{{ __('tracking.register.date_from') }}</label>
                <div class="batch-date-picker">
                    <input id="date_from" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="DD-MM-YYYY" readonly>
                    <input type="date" class="native-batch-date" data-target="date_from" tabindex="-1">
                    <i class="bi bi-calendar3"></i>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label" for="date_to">{{ __('tracking.register.date_to') }}</label>
                <div class="batch-date-picker">
                    <input id="date_to" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="DD-MM-YYYY" readonly>
                    <input type="date" class="native-batch-date" data-target="date_to" tabindex="-1">
                    <i class="bi bi-calendar3"></i>
                </div>
            </div>
            <div class="col-sm-6 col-md-3 d-grid"><button class="btn btn-brand"><i class="bi bi-search me-1"></i>Search</button></div>
            <div class="col-sm-6 col-md-3 d-grid"><a href="{{ route('admin.tracking.court.batches.index') }}" class="btn btn-light border">Clear</a></div>
        </div>
        </div>
    </form>

    <div class="table-responsive batch-table-wrap admin-panel">
        <table class="table align-middle mb-0">
            <thead><tr><th>Batch No</th><th>{{ __('tracking.court.court') }}</th><th>Date</th><th>Files</th><th>Status</th><th>Created By</th><th></th></tr></thead>
            <tbody>
            @forelse($batches as $batch)
                @php
                    $total = (int) $batch->items_count;
                    $returned = (int) $batch->returned_items_count;
                    $status = $batch->type === 'return' ? 'Received' : ($total === 0 ? 'Empty' : ($returned === $total ? 'Returned' : ($returned > 0 ? 'Partially Returned' : 'In Court')));
                    $statusClass = $batch->type === 'return' || $returned === $total && $total > 0 ? 'complete' : ($returned > 0 ? 'partial' : 'open');
                @endphp
                <tr>
                    <td><a class="batch-number" href="{{ route('admin.tracking.court.batches.show', $batch) }}">{{ $batch->batch_no }}</a><small>{{ ucfirst($batch->type) }}</small></td>
                    <td>{{ $batch->court?->displayName() ?? '-' }}</td>
                    <td>{{ optional($batch->dispatched_at ?? $batch->returned_at)->format('d-m-Y') }}<small>{{ optional($batch->dispatched_at ?? $batch->returned_at)->format('h:i A') }}</small></td>
                    <td>{{ $total }}@if($batch->type === 'dispatch' && $returned > 0)<small>{{ $returned }} returned</small>@endif</td>
                    <td><span class="batch-status {{ $statusClass }}">{{ $status }}</span></td>
                    <td>{{ $batch->createdBy?->name ?? '-' }}</td>
                    <td class="text-end"><a href="{{ route('admin.tracking.court.batches.show', $batch) }}" class="btn btn-action" title="View batch"><i class="bi bi-arrow-right"></i></a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        {{ $hasBatchSearch ? 'No batches found.' : 'Use the filters above to search court batches.' }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($batches->hasPages())
        <div class="pagination-strip">
            <a class="btn btn-sm btn-light border {{ $batches->onFirstPage() ? 'disabled' : '' }}" href="{{ $batches->previousPageUrl() ?? '#' }}">Previous</a>
            <span class="small text-muted">Page {{ $batches->currentPage() }} of {{ $batches->lastPage() }}</span>
            <a class="btn btn-sm btn-light border {{ $batches->hasMorePages() ? '' : 'disabled' }}" href="{{ $batches->nextPageUrl() ?? '#' }}">Next</a>
        </div>
    @endif
</div>
@endsection

@push('css')
<style>
    .batch-page { max-width: 1120px; }
    .batch-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .85rem 1rem; background: #fff; border: 1px solid #e3e8ef; border-top: 3px solid #00284d; border-bottom-color: #d4a017; border-radius: 4px; box-shadow: 0 1px 5px rgba(0, 40, 77, .08); }
    .batch-header h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
    .batch-header small { color: #6b7280; font-weight: 600; }
    .system-mark { color: #b87d08; font-size: .82rem; font-weight: 800; }
    .admin-panel { background: #fff; border: 1px solid #e3e8ef; border-radius: 4px; box-shadow: 0 1px 5px rgba(0, 40, 77, .07); overflow: hidden; }
    .panel-heading { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .75rem 1rem; background: #fbfcfe; border-top: 3px solid #00284d; border-bottom: 1px solid #e5e7eb; }
    .panel-heading h5 { margin: 0; color: #1f2937; font-size: 1rem; font-weight: 800; }
    .panel-heading span { color: #6b7280; font-size: .84rem; font-weight: 600; }
    .panel-body { padding: 1rem; }
    .batch-filters .form-label { margin-bottom: 4px; color: #374151; font-size: .78rem; font-weight: 800; }
    .batch-filters .form-control, .batch-filters .form-select { border-radius: 4px; min-height: 42px; }
    .batch-filters .form-control:focus, .batch-filters .form-select:focus { border-color: #d4a017; box-shadow: 0 0 0 .15rem rgba(212, 160, 23, .15); }
    .batch-date-picker { position: relative; }
    .batch-date-picker > .form-control { padding-right: 40px; background: #fff; }
    .native-batch-date { position: absolute; inset: 0; z-index: 2; width: 100%; opacity: 0; cursor: pointer; }
    .batch-date-picker > i { position: absolute; top: 50%; right: 13px; transform: translateY(-50%); color: #475569; }
    .batch-table-wrap th { background: #eef5fb; color: #00284d; font-size: .78rem; font-weight: 800; }
    .batch-table-wrap td, .batch-table-wrap th { padding: 11px 12px; border-color: #edf2f7; }
    .batch-table-wrap small { display: block; color: #64748b; font-size: .75rem; }
    .batch-number { color: #0f4c81; font-weight: 700; text-decoration: none; }
    .batch-status { display: inline-block; padding: 4px 7px; border-radius: 4px; font-size: .72rem; font-weight: 800; }
    .batch-status.open { background: #dbeafe; color: #1d4ed8; }
    .batch-status.partial { background: #fef3c7; color: #92400e; }
    .batch-status.complete { background: #dcfce7; color: #166534; }
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; }
    .btn-outline-brand { color: #00284d; border-color: #00284d; }
    .btn-action { width: 2rem; height: 2rem; display: inline-grid; place-items: center; padding: 0; border-radius: 4px; border: 1px solid #bcd0e2; color: #0b4f8a; background: #f2f7fc; }
    .btn-action:hover { background: #0b4f8a; color: #fff; }
    .pagination-strip { display: flex; justify-content: space-between; align-items: center; margin-top: .85rem; padding: .75rem 1rem; background: #fff; border: 1px solid #e3e8ef; border-radius: 4px; }
    @media (max-width: 767.98px) {
        .batch-page { padding-top: 1rem !important; }
        .batch-header { align-items: stretch; flex-direction: column; }
        .batch-header .btn { width: 100%; }
        .batch-table-wrap table { min-width: 880px; }
    }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.native-batch-date').forEach((picker) => {
        const target = document.getElementById(picker.dataset.target);
        if (/^\d{2}-\d{2}-\d{4}$/.test(target.value)) {
            const [day, month, year] = target.value.split('-');
            picker.value = `${year}-${month}-${day}`;
        }
        picker.addEventListener('click', function () { try { this.showPicker?.(); } catch (error) {} });
        picker.addEventListener('change', function () {
            const [year, month, day] = this.value.split('-');
            if (year && month && day) target.value = `${day}-${month}-${year}`;
        });
    });
});
</script>
@endpush
