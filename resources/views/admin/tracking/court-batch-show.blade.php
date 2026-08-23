@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 batch-detail-page">
    <div class="batch-header mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.tracking.court.batches.index') }}" class="btn btn-action" title="Back"><i class="bi bi-arrow-left"></i></a>
            <div>
                <div class="system-mark">RTFTS Court</div>
                <h4 class="mb-0">{{ $batch->batch_no }}</h4>
                <small>{{ ucfirst($batch->type) }} Batch</small>
            </div>
        </div>
        <a target="_blank" href="{{ route('admin.tracking.court.batch.pdf', $batch) }}" class="btn btn-gold btn-sm"><i class="bi bi-printer me-1"></i>{{ __('tracking.court.print_batch_pdf') }}</a>
    </div>

    <section class="batch-meta mb-3">
        <div><span>{{ __('tracking.court.court') }}</span><strong>{{ $batch->court?->displayName() ?? '-' }}</strong></div>
        <div><span>Date</span><strong>{{ optional($batch->dispatched_at ?? $batch->returned_at)->format('d-m-Y h:i A') }}</strong></div>
        <div><span>Created By</span><strong>{{ $batch->createdBy?->name ?? '-' }}</strong></div>
        <div><span>Total Files</span><strong>{{ $batch->items->count() }}</strong></div>
    </section>

    @if($batch->received_by_name || $batch->received_by_designation || $batch->received_by_phone)
        <div class="handover-line mb-3">
            <i class="bi bi-person-check"></i>
            <div><strong>{{ $batch->received_by_name ?? '-' }}</strong><small>{{ collect([$batch->received_by_designation, $batch->received_by_phone])->filter()->join(' | ') }}</small></div>
        </div>
    @endif

    <div class="table-responsive batch-items admin-panel">
        <table class="table align-middle mb-0">
            <thead><tr><th>#</th><th>Case No</th><th>{{ __('tracking.register.from') }}</th><th>{{ __('tracking.register.to') }}</th><th>Processed</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($batch->items as $i => $item)
                @php($returnMovement = $itemReturns->get($item->id))
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><span class="case-link">{{ $item->courtCase?->case_reference ?? ('CASE-' . $item->case_id) }}</span></td>
                    <td><span class="section-badge">{{ $item->from_section ?? '-' }}</span></td>
                    <td><span class="section-badge">{{ $item->to_section ?? '-' }}</span></td>
                    <td>{{ optional($item->processed_at)->format('d-m-Y h:i A') }}</td>
                    <td>
                        @if($batch->type === 'return')
                            <span class="item-status returned">Received</span>
                        @elseif($returnMovement)
                            <span class="item-status returned">Returned</span><small>{{ optional($returnMovement->received_at)->format('d-m-Y h:i A') }}</small>
                        @else
                            <span class="item-status court">In Court</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">No files in this batch.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($batch->notes)
        <div class="batch-notes mt-3"><strong>{{ __('tracking.court.notes_label') }}:</strong> {{ $batch->notes }}</div>
    @endif
</div>
@endsection

@push('css')
<style>
    .batch-detail-page { max-width: 1080px; }
    .batch-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .85rem 1rem; background: #fff; border: 1px solid #e3e8ef; border-top: 3px solid #00284d; border-bottom-color: #d4a017; border-radius: 4px; box-shadow: 0 1px 5px rgba(0, 40, 77, .08); }
    .batch-header h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
    .batch-header small { color: #6b7280; font-weight: 600; }
    .system-mark { color: #b87d08; font-size: .82rem; font-weight: 800; }
    .admin-panel { background: #fff; border: 1px solid #e3e8ef; border-radius: 4px; box-shadow: 0 1px 5px rgba(0, 40, 77, .07); overflow: hidden; }
    .btn-action { width: 2rem; height: 2rem; display: inline-grid; place-items: center; padding: 0; border-radius: 4px; border: 1px solid #bcd0e2; color: #0b4f8a; background: #f2f7fc; }
    .btn-action:hover { background: #0b4f8a; color: #fff; }
    .batch-meta { display: grid; grid-template-columns: repeat(4, 1fr); border: 1px solid #e2e8f0; border-left: 4px solid #d4a017; border-radius: 4px; background: #fff; box-shadow: 0 1px 5px rgba(0, 40, 77, .06); }
    .batch-meta > div { padding: 16px; border-right: 1px solid #e2e8f0; }
    .batch-meta > div:last-child { border-right: 0; }
    .batch-meta span, .handover-line small, .batch-items small { display: block; color: #64748b; font-size: .75rem; }
    .batch-meta strong { display: block; margin-top: 2px; color: #1f2937; }
    .handover-line { display: flex; align-items: center; gap: 12px; padding: 12px 15px; border-radius: 4px; border: 1px solid #bce8ca; background: #f0fdfa; color: #115e59; }
    .handover-line > i { font-size: 1.25rem; }
    .batch-items th { background: #eef5fb; color: #00284d; font-size: .78rem; font-weight: 800; }
    .batch-items td, .batch-items th { padding: 11px 12px; border-color: #edf2f7; }
    .case-link { color: #0f4c81; font-weight: 700; text-decoration: none; }
    .item-status { display: inline-block; padding: 4px 7px; border-radius: 4px; font-size: .72rem; font-weight: 750; }
    .item-status.court { background: #dbeafe; color: #1d4ed8; }
    .item-status.returned { background: #dcfce7; color: #166534; }
    .section-badge { display: inline-flex; border-radius: 4px; border: 1px solid #d8e3ef; background: #f7fbff; color: #0b4f8a; font-size: .76rem; font-weight: 800; padding: .18rem .45rem; white-space: nowrap; }
    .batch-notes { padding: 12px 14px; border-left: 4px solid #94a3b8; background: #f8fafc; color: #475569; }
    .btn-gold { background: #d4a017; color: #17202a; border-color: #d4a017; font-weight: 800; }
    .btn-gold:hover { background: #bc8d12; color: #fff; border-color: #bc8d12; }
    @media (max-width: 767.98px) {
        .batch-header { align-items: stretch; flex-direction: column; }
        .batch-header .btn-gold { width: 100%; }
        .batch-meta { grid-template-columns: 1fr 1fr; }
        .batch-meta > div:nth-child(2) { border-right: 0; }
        .batch-meta > div { border-bottom: 1px solid #e2e8f0; }
        .batch-items table { min-width: 820px; }
    }
</style>
@endpush
