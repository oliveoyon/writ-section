@extends('admin.layouts.adminlayout')

@section('content')
@php
    $isSender = (int)$transferBatch->sender_user_id === (int)auth()->id();
    $isRecipient = (int)$transferBatch->recipient_user_id === (int)auth()->id();
    $backDirection = $isSender ? 'outgoing' : ($isRecipient ? 'incoming' : 'all');
    $statusClass = match($transferBatch->status) {
        \App\Models\FileTransferBatch::STATUS_PENDING => 'status-pending',
        \App\Models\FileTransferBatch::STATUS_PARTIALLY_RECEIVED => 'status-partial',
        \App\Models\FileTransferBatch::STATUS_COMPLETED => 'status-completed',
        default => 'status-cancelled',
    };
@endphp
<div class="container py-4 handover-detail-page">
    <div class="page-header mb-3">
        <div>
            <div class="system-mark">RTFTS Handover</div>
            <h4 class="mb-0">{{ $transferBatch->batch_no }}</h4>
        </div>
        <div class="header-actions">
            @if($isRecipient && $transferBatch->pending_items_count > 0)
                <a href="{{ route('admin.tracking.section.receive') }}" class="btn btn-receive btn-sm">
                    <i class="bi bi-upc-scan" aria-hidden="true"></i> Receive Files
                </a>
            @endif
            <a href="{{ route('admin.tracking.handover.index', ['direction' => $backDirection]) }}" class="btn btn-light btn-sm border">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Handovers
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="summary-band mb-3">
        <div class="party-flow">
            <span>
                <small>Sent By</small>
                <strong>{{ $transferBatch->sender_name }}</strong>
                <em>{{ $transferBatch->sender_section ?: '-' }}</em>
            </span>
            <i class="bi bi-arrow-right" aria-hidden="true"></i>
            <span>
                <small>Sent To</small>
                <strong>{{ $transferBatch->recipient_name }}</strong>
                <em>{{ $transferBatch->recipient_section ?: '-' }}</em>
            </span>
        </div>
        <div class="summary-facts">
            <div><small>Sent</small><strong>{{ $transferBatch->sent_at->format('d-m-Y h:i A') }}</strong></div>
            <div><small>Total</small><strong>{{ $transferBatch->items_count }}</strong></div>
            <div><small>Pending</small><strong>{{ $transferBatch->pending_items_count }}</strong></div>
            <div><small>Received</small><strong>{{ $transferBatch->received_items_count }}</strong></div>
            <div><small>Status</small><strong><span class="status-badge {{ $statusClass }}">{{ $transferBatch->status_label }}</span></strong></div>
        </div>
    </section>

    <section class="items-panel">
        <div class="items-heading">
            <strong>Files</strong>
            @if($canCancel && $transferBatch->pending_items_count > 0)
                <button type="button" class="btn btn-outline-danger btn-sm cancel-trigger"
                        data-action="{{ route('admin.tracking.handover.cancel', $transferBatch) }}"
                        data-title="Cancel all remaining pending files">
                    <i class="bi bi-x-circle" aria-hidden="true"></i> Cancel Pending
                </button>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 item-table">
                <thead>
                    <tr>
                        <th>Case No.</th>
                        <th>Status</th>
                        <th>Sent</th>
                        <th>Received / Waiting</th>
                        <th>Processed By</th>
                        @if($canCancel)<th class="text-end">Action</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        @php
                            $itemClass = match($item->status) {
                                \App\Models\FileTransferItem::STATUS_PENDING => 'status-pending',
                                \App\Models\FileTransferItem::STATUS_RECEIVED => 'status-completed',
                                default => 'status-cancelled',
                            };
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $item->courtCase?->case_reference ?: '-' }}</strong>
                                <small>{{ $item->courtCase?->permanent_barcode ?: '-' }}</small>
                            </td>
                            <td><span class="status-badge {{ $itemClass }}">{{ $item->status_label }}</span></td>
                            <td>
                                <strong>{{ $item->sent_at->format('d-m-Y') }}</strong>
                                <small>{{ $item->sent_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                @if($item->received_at)
                                    <strong>{{ $item->sent_at->diffForHumans($item->received_at, true, true, 2) }}</strong>
                                    <small>{{ $item->received_at->format('d-m-Y h:i A') }}</small>
                                @elseif($item->cancelled_at)
                                    <strong>Cancelled</strong>
                                    <small>{{ $item->cancelled_at->format('d-m-Y h:i A') }}</small>
                                @else
                                    <strong class="waiting-text">{{ $item->sent_at->diffForHumans(now(), true, true, 2) }}</strong>
                                    <small>Not received yet</small>
                                @endif
                            </td>
                            <td>
                                @if($item->receivedBy)
                                    <strong>{{ $item->receivedBy->name }}</strong>
                                    <small>{{ $item->receivedBy->employee_id ?: '-' }}</small>
                                @elseif($item->cancelledBy)
                                    <strong>{{ $item->cancelledBy->name }}</strong>
                                    <small>{{ $item->cancellation_reason }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            @if($canCancel)
                                <td class="text-end">
                                    @if($item->status === \App\Models\FileTransferItem::STATUS_PENDING)
                                        <button type="button" class="btn btn-sm btn-outline-danger cancel-trigger"
                                                data-action="{{ route('admin.tracking.handover.item.cancel', [$transferBatch, $item]) }}"
                                                data-title="Cancel {{ $item->courtCase?->case_reference ?: 'this file' }}">
                                            <i class="bi bi-x-lg" aria-hidden="true"></i><span class="visually-hidden">Cancel</span>
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    @if($items->hasPages())
        <div class="pagination-wrap">{{ $items->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

@if($canCancel)
<div class="modal fade" id="cancelHandoverModal" tabindex="-1" aria-labelledby="cancelHandoverTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="cancelHandoverForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelHandoverTitle">Cancel Pending File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="cancelReason" class="form-label">Reason</label>
                    <textarea id="cancelReason" name="reason" class="form-control" rows="3" maxlength="1000" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Keep File</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('css')
<style>
    .handover-detail-page { max-width:1120px; }
    .page-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.85rem 1rem; background:#fff; border:1px solid #e3e8ef; border-top:3px solid #00284d; border-bottom-color:#d4a017; border-radius:4px; box-shadow:0 1px 5px rgba(0,40,77,.08); }
    .page-header h4 { color:#00284d; font-size:1.15rem; font-weight:800; font-family:monospace; }
    .system-mark { color:#b87d08; font-size:.82rem; font-weight:800; }
    .header-actions { display:flex; flex-wrap:wrap; gap:.5rem; }
    .btn-receive { display:inline-flex; align-items:center; gap:.35rem; color:#fff; background:#187246; border-color:#187246; font-weight:800; }
    .btn-receive:hover { color:#fff; background:#115c37; }
    .summary-band { overflow:hidden; background:#fff; border:1px solid #dbe3ec; border-radius:4px; }
    .party-flow { display:grid; grid-template-columns:minmax(0,1fr) 30px minmax(0,1fr); align-items:center; gap:.75rem; padding:1rem; color:#fff; background:#0b5f78; }
    .party-flow > span { min-width:0; }
    .party-flow small, .party-flow strong, .party-flow em { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .party-flow small { color:rgba(255,255,255,.72); font-size:.72rem; font-weight:800; text-transform:uppercase; }
    .party-flow strong { font-weight:800; }
    .party-flow em { color:rgba(255,255,255,.82); font-size:.78rem; font-style:normal; }
    .party-flow > i { text-align:center; color:#efb929; font-size:1.2rem; }
    .summary-facts { display:grid; grid-template-columns:1.4fr repeat(4,minmax(80px,.65fr)); }
    .summary-facts div { padding:.7rem .85rem; border-right:1px solid #e5e7eb; }
    .summary-facts div:last-child { border-right:0; }
    .summary-facts small, .summary-facts strong { display:block; }
    .summary-facts small { color:#6b7280; font-size:.7rem; font-weight:800; text-transform:uppercase; }
    .summary-facts strong { margin-top:.15rem; color:#1f2937; font-size:.84rem; }
    .items-panel { overflow:hidden; background:#fff; border:1px solid #e3e8ef; border-radius:4px; box-shadow:0 1px 5px rgba(0,40,77,.06); }
    .items-heading { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.7rem .85rem; border-top:3px solid #00284d; border-bottom:1px solid #e5e7eb; }
    .items-heading strong { color:#00284d; font-weight:800; }
    .item-table { min-width:900px; }
    .item-table thead th { padding:.65rem .75rem; color:#00284d; background:#eef5fb; border-bottom:0; font-size:.76rem; font-weight:800; white-space:nowrap; }
    .item-table td { padding:.65rem .75rem; }
    .item-table td strong, .item-table td small { display:block; }
    .item-table td strong { color:#1f2937; font-size:.82rem; }
    .item-table td small { margin-top:.1rem; color:#6b7280; font-size:.72rem; }
    .waiting-text { color:#8a5a12 !important; }
    .status-badge { display:inline-flex; padding:.22rem .45rem; border-radius:4px; font-size:.7rem; font-weight:800; white-space:nowrap; }
    .status-pending { color:#8a5a12; background:#fff3cd; }
    .status-partial { color:#075985; background:#e0f2fe; }
    .status-completed { color:#166534; background:#dcfce7; }
    .status-cancelled { color:#991b1b; background:#fee2e2; }
    .pagination-wrap { display:flex; justify-content:flex-end; padding-top:1rem; }
    .pagination-wrap svg { width:1rem; height:1rem; }
    .form-label { color:#374151; font-weight:800; }
    .form-control:focus { border-color:#d4a017; box-shadow:0 0 0 .15rem rgba(212,160,23,.15); }
    @media(max-width:767.98px) { .handover-detail-page { padding-top:1rem !important; } .page-header { align-items:stretch; flex-direction:column; } .summary-facts { grid-template-columns:repeat(2,minmax(0,1fr)); } .summary-facts div { border-bottom:1px solid #e5e7eb; } .party-flow { grid-template-columns:1fr; } .party-flow > i { transform:rotate(90deg); } }
</style>
@endpush

@if($canCancel)
@push('js')
<script>
    (function () {
        const modalElement = document.getElementById('cancelHandoverModal');
        const modal = new bootstrap.Modal(modalElement);
        const form = document.getElementById('cancelHandoverForm');
        const title = document.getElementById('cancelHandoverTitle');
        const reason = document.getElementById('cancelReason');

        document.querySelectorAll('.cancel-trigger').forEach(function (button) {
            button.addEventListener('click', function () {
                form.action = button.dataset.action;
                title.textContent = button.dataset.title;
                reason.value = '';
                modal.show();
                modalElement.addEventListener('shown.bs.modal', function focusReason() {
                    reason.focus();
                    modalElement.removeEventListener('shown.bs.modal', focusReason);
                });
            });
        });
    })();
</script>
@endpush
@endif
