@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 batch-page">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-collection batch-heading-icon"></i>
            <h3 class="mb-0">Court Batches</h3>
        </div>
        <a href="{{ route('admin.tracking.court.dispatch.index') }}" class="btn btn-brand"><i class="bi bi-box-arrow-up-right me-1"></i>{{ __('tracking.receive.send_to_court') }}</a>
    </div>

    <form method="GET" class="batch-filters mb-4">
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
    </form>

    <div class="table-responsive batch-table-wrap">
        <table class="table align-middle mb-0">
            <thead><tr><th>Batch No</th><th>{{ __('tracking.court.court') }}</th><th>Date</th><th>Files</th><th>Status</th><th>Created By</th><th></th></tr></thead>
            <tbody>
            @forelse($batches as $batch)
                @php
                    $total = $batch->items->count();
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
                    <td class="text-end"><a href="{{ route('admin.tracking.court.batches.show', $batch) }}" class="btn btn-sm btn-outline-brand" title="View batch"><i class="bi bi-arrow-right"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-5">No batches found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($batches->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <a class="btn btn-sm btn-light border {{ $batches->onFirstPage() ? 'disabled' : '' }}" href="{{ $batches->previousPageUrl() ?? '#' }}">Previous</a>
            <span class="small text-muted">Page {{ $batches->currentPage() }} of {{ $batches->lastPage() }}</span>
            <a class="btn btn-sm btn-light border {{ $batches->hasMorePages() ? '' : 'disabled' }}" href="{{ $batches->nextPageUrl() ?? '#' }}">Next</a>
        </div>
    @endif
</div>
@endsection

@push('css')
<style>
    .batch-page { max-width: 1180px; }
    .batch-page h3 { font-size: 1.4rem; font-weight: 700; }
    .batch-heading-icon { color: #0f766e; font-size: 1.45rem; }
    .batch-filters { padding: 18px; border: 1px solid #e2e8f0; border-radius: 6px; background: #f8fafc; }
    .batch-filters .form-label { margin-bottom: 4px; color: #475569; font-size: .78rem; font-weight: 650; }
    .batch-date-picker { position: relative; }
    .batch-date-picker > .form-control { padding-right: 40px; background: #fff; }
    .native-batch-date { position: absolute; inset: 0; z-index: 2; width: 100%; opacity: 0; cursor: pointer; }
    .batch-date-picker > i { position: absolute; top: 50%; right: 13px; transform: translateY(-50%); color: #475569; }
    .batch-table-wrap { border: 1px solid #e2e8f0; border-radius: 6px; }
    .batch-table-wrap th { background: #f8fafc; color: #64748b; font-size: .75rem; text-transform: uppercase; }
    .batch-table-wrap td, .batch-table-wrap th { padding: 11px 12px; border-color: #edf2f7; }
    .batch-table-wrap small { display: block; color: #64748b; font-size: .75rem; }
    .batch-number { color: #0f4c81; font-weight: 700; text-decoration: none; }
    .batch-status { display: inline-block; padding: 4px 7px; border-radius: 4px; font-size: .72rem; font-weight: 750; }
    .batch-status.open { background: #dbeafe; color: #1d4ed8; }
    .batch-status.partial { background: #fef3c7; color: #92400e; }
    .batch-status.complete { background: #dcfce7; color: #166534; }
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; }
    .btn-outline-brand { color: #00284d; border-color: #00284d; }
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
