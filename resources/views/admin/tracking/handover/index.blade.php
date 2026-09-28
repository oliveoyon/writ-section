@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 handover-list-page">
    <div class="page-header mb-3">
        <div>
            <div class="system-mark">RTFTS File Desk</div>
            <h4 class="mb-0">File Handovers</h4>
        </div>
        <a href="{{ route('admin.tracking.handover.workspace') }}" class="btn btn-light btn-sm border">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> File Desk
        </a>
    </div>

    <div class="toolbar mb-3">
        <nav class="handover-tabs" aria-label="Handover direction">
            <a class="tab-link {{ $direction === 'incoming' ? 'active' : '' }}" href="{{ route('admin.tracking.handover.index', ['direction' => 'incoming']) }}">
                <i class="bi bi-box-arrow-in-down" aria-hidden="true"></i> Incoming
            </a>
            <a class="tab-link {{ $direction === 'outgoing' ? 'active' : '' }}" href="{{ route('admin.tracking.handover.index', ['direction' => 'outgoing']) }}">
                <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Sent
            </a>
            @if($isSuperAdmin)
                <a class="tab-link {{ $direction === 'all' ? 'active' : '' }}" href="{{ route('admin.tracking.handover.index', ['direction' => 'all']) }}">
                    <i class="bi bi-shield-check" aria-hidden="true"></i> All
                </a>
            @endif
        </nav>

        <form method="GET" class="batch-search">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <label class="visually-hidden" for="handoverSearch">Batch or Case No.</label>
            <div class="input-group">
                <input id="handoverSearch" type="search" name="q" class="form-control" value="{{ $search }}" placeholder="Batch or Case No.">
                <button class="btn btn-brand" title="Search"><i class="bi bi-search" aria-hidden="true"></i></button>
                @if($search !== '')
                    <a class="btn btn-outline-secondary" href="{{ route('admin.tracking.handover.index', ['direction' => $direction]) }}" title="Clear"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>

    <div class="handover-table-wrap">
        <div class="table-responsive">
            <table class="table align-middle mb-0 handover-table">
                <thead>
                    <tr>
                        <th>Batch</th>
                        <th>{{ $direction === 'outgoing' ? 'Sent To' : ($direction === 'incoming' ? 'Sent By' : 'From / To') }}</th>
                        <th>Files</th>
                        <th>Status</th>
                        <th>Sent</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        @php
                            $statusClass = match($batch->status) {
                                \App\Models\FileTransferBatch::STATUS_PENDING => 'status-pending',
                                \App\Models\FileTransferBatch::STATUS_PARTIALLY_RECEIVED => 'status-partial',
                                \App\Models\FileTransferBatch::STATUS_COMPLETED => 'status-completed',
                                default => 'status-cancelled',
                            };
                        @endphp
                        <tr>
                            <td><strong class="batch-number">{{ $batch->batch_no }}</strong></td>
                            <td>
                                @if($direction === 'outgoing')
                                    <strong>{{ $batch->recipient_name }}</strong>
                                    <small>{{ $batch->recipient_section ?: '-' }}</small>
                                @elseif($direction === 'incoming')
                                    <strong>{{ $batch->sender_name }}</strong>
                                    <small>{{ $batch->sender_section ?: '-' }}</small>
                                @else
                                    <strong>{{ $batch->sender_name }} <i class="bi bi-arrow-right"></i> {{ $batch->recipient_name }}</strong>
                                    <small>{{ $batch->sender_section ?: '-' }} / {{ $batch->recipient_section ?: '-' }}</small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $batch->items_count }}</strong>
                                <small>{{ $batch->pending_items_count }} pending, {{ $batch->received_items_count }} received</small>
                            </td>
                            <td><span class="status-badge {{ $statusClass }}">{{ $batch->status_label }}</span></td>
                            <td>
                                <strong>{{ $batch->sent_at->format('d-m-Y') }}</strong>
                                <small>{{ $batch->sent_at->format('h:i A') }}</small>
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-view" href="{{ route('admin.tracking.handover.show', $batch) }}" title="View handover">
                                    <i class="bi bi-eye" aria-hidden="true"></i><span>View</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-row">No handovers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($batches->hasPages())
        <div class="pagination-wrap">{{ $batches->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection

@push('css')
<style>
    .handover-list-page { max-width:1120px; }
    .page-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.85rem 1rem; background:#fff; border:1px solid #e3e8ef; border-top:3px solid #00284d; border-bottom-color:#d4a017; border-radius:4px; box-shadow:0 1px 5px rgba(0,40,77,.08); }
    .page-header h4 { color:#00284d; font-size:1.15rem; font-weight:800; }
    .system-mark { color:#b87d08; font-size:.82rem; font-weight:800; }
    .toolbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.7rem; background:#fff; border:1px solid #e3e8ef; border-radius:4px; }
    .handover-tabs { display:flex; gap:.35rem; }
    .tab-link { display:inline-flex; align-items:center; gap:.35rem; min-height:40px; padding:.45rem .75rem; color:#475569; text-decoration:none; border:1px solid #dbe3ec; border-radius:4px; font-weight:800; }
    .tab-link:hover { color:#00284d; border-color:#9eb5ca; }
    .tab-link.active { color:#fff; background:#0b5f78; border-color:#0b5f78; }
    .batch-search { width:min(100%,360px); }
    .btn-brand { color:#fff; background:#00284d; border-color:#00284d; }
    .btn-brand:hover { color:#fff; background:#001e3a; }
    .handover-table-wrap { overflow:hidden; background:#fff; border:1px solid #e3e8ef; border-radius:4px; box-shadow:0 1px 5px rgba(0,40,77,.06); }
    .handover-table { min-width:850px; }
    .handover-table thead th { padding:.7rem .75rem; color:#00284d; background:#eef5fb; border-bottom:0; font-size:.78rem; font-weight:800; white-space:nowrap; }
    .handover-table td { padding:.7rem .75rem; }
    .handover-table td strong, .handover-table td small { display:block; }
    .handover-table td strong { color:#1f2937; font-size:.86rem; }
    .handover-table td small { margin-top:.12rem; color:#6b7280; font-size:.74rem; }
    .batch-number { color:#0b5f78 !important; font-family:monospace; white-space:nowrap; }
    .status-badge { display:inline-flex; padding:.22rem .45rem; border-radius:4px; font-size:.72rem; font-weight:800; white-space:nowrap; }
    .status-pending { color:#8a5a12; background:#fff3cd; }
    .status-partial { color:#075985; background:#e0f2fe; }
    .status-completed { color:#166534; background:#dcfce7; }
    .status-cancelled { color:#991b1b; background:#fee2e2; }
    .btn-view { display:inline-flex; align-items:center; gap:.3rem; color:#fff; background:#0b5f78; border-color:#0b5f78; font-weight:800; }
    .btn-view:hover { color:#fff; background:#084b61; }
    .empty-row { padding:2rem !important; color:#6b7280; text-align:center; }
    .pagination-wrap { display:flex; justify-content:flex-end; padding-top:1rem; }
    .pagination-wrap svg { width:1rem; height:1rem; }
    @media(max-width:767.98px) { .handover-list-page { padding-top:1rem !important; } .toolbar { align-items:stretch; flex-direction:column; } .handover-tabs, .batch-search { width:100%; } .handover-tabs { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); } .tab-link { justify-content:center; } }
</style>
@endpush
