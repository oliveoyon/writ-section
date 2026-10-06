@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 handover-home">
    <div class="desk-header mb-3">
        <div>
            <div class="system-mark">RTFTS File Desk</div>
            <h4 class="mb-0">{{ auth()->user()->name }}</h4>
            <small>{{ $section }}</small>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.tracking.handover.index') }}" class="btn btn-handovers btn-sm">
                <i class="bi bi-arrow-left-right" aria-hidden="true"></i> Handovers
            </a>
            @if($isFiling)
                <a href="{{ route('admin.tracking.filing.scan-temp') }}" class="btn btn-filing btn-sm">
                    <i class="bi bi-folder-plus" aria-hidden="true"></i> Filing
                </a>
            @endif
            <a href="{{ route('admin.tracking.register-report') }}" class="btn btn-report btn-sm">
                <i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i> Report
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="desk-layout">
        <div class="action-grid">
            <a class="desk-action send-action" href="{{ route('admin.tracking.handover.recipients') }}">
                <span class="action-icon"><i class="bi bi-send" aria-hidden="true"></i></span>
                <span class="action-copy">
                    <strong>Send Files</strong>
                    <span>{{ number_format($heldFiles) }} in your custody</span>
                </span>
                @if($outgoingPending > 0)
                    <span class="action-count">{{ number_format($outgoingPending) }} waiting</span>
                @endif
                <i class="bi bi-chevron-right action-arrow" aria-hidden="true"></i>
            </a>

            <a class="desk-action receive-action" href="{{ $receiveRoute }}">
                <span class="action-icon"><i class="bi bi-upc-scan" aria-hidden="true"></i></span>
                <span class="action-copy">
                    <strong>Receive Files</strong>
                    <span>Scan Case No. or barcode</span>
                </span>
                @if($incomingPending > 0)
                    <span class="action-count">{{ number_format($incomingPending) }} for you</span>
                @endif
                <i class="bi bi-chevron-right action-arrow" aria-hidden="true"></i>
            </a>
        </div>

        <aside class="notification-column" aria-label="File handover notifications">
            @if($incomingPending > 0)
                <section class="notice-panel incoming-panel" aria-labelledby="incomingHeading">
                    <div class="notice-heading incoming-heading">
                        <span class="notice-heading-icon"><i class="bi bi-bell-fill" aria-hidden="true"></i></span>
                        <span>
                            <strong id="incomingHeading">Files Waiting for You</strong>
                            <small>{{ number_format($incomingPending) }} {{ $incomingPending === 1 ? 'file needs' : 'files need' }} to be received</small>
                        </span>
                        <a href="{{ $receiveRoute }}" class="btn btn-receive-now">
                            <i class="bi bi-upc-scan" aria-hidden="true"></i> Receive
                        </a>
                    </div>

                    <div class="notice-list">
                        @foreach($incomingBatches as $batch)
                            <a class="notice-row" href="{{ route('admin.tracking.handover.show', $batch) }}">
                                <span class="person-avatar incoming-avatar"><i class="bi bi-person" aria-hidden="true"></i></span>
                                <span class="person-copy">
                                    <strong>{{ $batch->sender_name }}</strong>
                                    <span>{{ $batch->sender_section ?: 'Unassigned Section' }}</span>
                                </span>
                                <span class="batch-copy incoming-batch">
                                    <strong>{{ $batch->pending_items_count }} {{ $batch->pending_items_count === 1 ? 'file' : 'files' }}</strong>
                                    <span>{{ $batch->batch_no }}</span>
                                </span>
                                <span class="waiting-copy">
                                    <strong>{{ $batch->sent_at->diffForHumans(now(), true, true, 2) }}</strong>
                                    <span>{{ $batch->sent_at->format('d-m-Y h:i A') }}</span>
                                </span>
                                <i class="bi bi-chevron-right row-arrow" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>

                    <a class="more-batches" href="{{ route('admin.tracking.handover.index', ['direction' => 'incoming']) }}">
                        {{ $hasMoreIncomingBatches ? 'View all incoming handovers' : 'View incoming history' }}
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </section>
            @endif

            @if($outgoingPending > 0)
                <section class="notice-panel outgoing-panel" aria-labelledby="outgoingHeading">
                    <div class="notice-heading outgoing-heading">
                        <span class="notice-heading-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
                        <span>
                            <strong id="outgoingHeading">Waiting for Receipt</strong>
                            <small>{{ number_format($outgoingPending) }} {{ $outgoingPending === 1 ? 'sent file is' : 'sent files are' }} still pending</small>
                        </span>
                    </div>

                    <div class="notice-list">
                        @foreach($outgoingBatches as $batch)
                            <a class="notice-row" href="{{ route('admin.tracking.handover.show', $batch) }}">
                                <span class="person-avatar outgoing-avatar"><i class="bi bi-person" aria-hidden="true"></i></span>
                                <span class="person-copy">
                                    <strong>{{ $batch->recipient_name }}</strong>
                                    <span>{{ $batch->recipient_section ?: 'Unassigned Section' }}</span>
                                </span>
                                <span class="batch-copy outgoing-batch">
                                    <strong>{{ $batch->pending_items_count }} not received</strong>
                                    <span>{{ $batch->batch_no }}</span>
                                </span>
                                <span class="waiting-copy">
                                    <strong>{{ $batch->sent_at->diffForHumans(now(), true, true, 2) }}</strong>
                                    <span>{{ $batch->sent_at->format('d-m-Y h:i A') }}</span>
                                </span>
                                <i class="bi bi-chevron-right row-arrow" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>

                    <a class="more-batches outgoing-more" href="{{ route('admin.tracking.handover.index', ['direction' => 'outgoing']) }}">
                        {{ $hasMoreOutgoingBatches ? 'View all sent handovers' : 'View sent history' }}
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </section>
            @endif

            @if($incomingPending === 0 && $outgoingPending === 0)
                <div class="notice-empty">
                    <i class="bi bi-check2-circle" aria-hidden="true"></i>
                    <strong>No pending handovers</strong>
                    <span>Your incoming and sent files are up to date.</span>
                </div>
            @endif
        </aside>
    </div>

    <section class="custody-register" id="custody-files" aria-labelledby="custodyHeading">
        <div class="custody-heading">
            <div>
                <div class="system-mark">Current Responsibility</div>
                <h5 id="custodyHeading">Files in My Custody</h5>
            </div>
            <span class="custody-total">{{ number_format($heldFiles) }} {{ $heldFiles === 1 ? 'file' : 'files' }}</span>
        </div>

        <form method="GET" action="{{ route('admin.tracking.handover.workspace') }}#custody-files" class="custody-search" role="search">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                <input
                    type="search"
                    name="custody_q"
                    class="form-control"
                    value="{{ $custodySearch }}"
                    maxlength="100"
                    placeholder="Search Case No., barcode, party, lawyer or case type"
                    aria-label="Search files in my custody"
                >
                <button class="btn btn-custody-search" type="submit">Search</button>
                @if($custodySearch !== '')
                    <a class="btn btn-outline-secondary" href="{{ route('admin.tracking.handover.workspace') }}#custody-files" title="Clear search" aria-label="Clear search">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
        </form>

        @if($custodySearch !== '')
            <div class="custody-result-line">
                {{ number_format($heldCases->total()) }} {{ $heldCases->total() === 1 ? 'file' : 'files' }} found
            </div>
        @endif

        <div class="custody-list">
            @forelse($heldCases as $case)
                @php
                    $petitioner = $case->petitioners->first()?->name_or_organization;
                    $respondent = $case->respondents->first()?->name_or_organization;
                    $pendingTransfer = $case->activeTransferItem;
                @endphp
                <article class="custody-row">
                    <span class="custody-case-icon"><i class="bi bi-folder2-open" aria-hidden="true"></i></span>
                    <span class="custody-case-main">
                        <strong>{{ $case->case_reference ?: $case->final_case_number }}</strong>
                        <span>{{ $case->case_type ?: 'Case type not recorded' }}</span>
                    </span>
                    <span class="custody-parties">
                        <strong>{{ $petitioner ?: 'Petitioner not recorded' }}</strong>
                        <span>vs {{ $respondent ?: 'Respondent not recorded' }}</span>
                    </span>
                    <span class="custody-meta">
                        @if($pendingTransfer)
                            <strong class="pending-custody"><i class="bi bi-hourglass-split" aria-hidden="true"></i> Sent to {{ $pendingTransfer->batch?->recipient_name ?: 'recipient' }}</strong>
                            <span>{{ $pendingTransfer->batch?->batch_no }}</span>
                        @else
                            <strong>{{ $case->current_holder_at?->format('d-m-Y') ?: '-' }}</strong>
                            <span>In custody</span>
                        @endif
                    </span>
                </article>
            @empty
                <div class="custody-empty">
                    <i class="bi bi-folder2" aria-hidden="true"></i>
                    <strong>{{ $custodySearch !== '' ? 'No matching file found' : 'No file is currently in your custody' }}</strong>
                </div>
            @endforelse
        </div>

        @if($heldCases->hasPages())
            <div class="custody-pagination">
                {{ $heldCases->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </section>
</div>
@endsection

@push('css')
<style>
    .handover-home { max-width: 1160px; }
    .desk-header {
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
    .desk-header h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
    .desk-header small { color: #6b7280; font-weight: 700; }
    .system-mark { color: #b87d08; font-size: .82rem; font-weight: 800; }
    .btn-report { display: inline-flex; align-items: center; gap: .4rem; color: #fff; background: #2563eb; border-color: #2563eb; font-weight: 800; border-radius: 4px; }
    .btn-report:hover, .btn-report:focus-visible { color: #fff; background: #1d4ed8; border-color: #1d4ed8; }
    .btn-handovers { display:inline-flex; align-items:center; gap:.4rem; color:#fff; background:#0b5f78; border-color:#0b5f78; font-weight:800; border-radius:4px; }
    .btn-handovers:hover, .btn-handovers:focus-visible { color:#fff; background:#084b5e; border-color:#084b5e; }
    .header-actions { display:flex; flex-wrap:wrap; gap:.5rem; }
    .btn-filing { display:inline-flex; align-items:center; gap:.4rem; color:#fff; background:#8a5a12; border-color:#8a5a12; font-weight:800; border-radius:4px; }
    .btn-filing:hover, .btn-filing:focus-visible { color:#fff; background:#71480d; border-color:#71480d; }
    .desk-layout { display:grid; grid-template-columns:minmax(320px,.72fr) minmax(0,1.28fr); align-items:start; gap:1rem; }
    .notification-column { display:grid; gap:1rem; min-width:0; }
    .notice-panel { overflow:hidden; background:#fff; border-radius:4px; box-shadow:0 3px 10px rgba(0,40,77,.11); }
    .incoming-panel { border:2px solid #d4a017; }
    .outgoing-panel { border:2px solid #3b82a0; }
    .notice-heading { display:grid; grid-template-columns:40px minmax(0,1fr) auto; align-items:center; gap:.7rem; padding:.75rem .85rem; border-bottom:1px solid; }
    .incoming-heading { background:#fff8e5; border-bottom-color:#ead9a7; }
    .outgoing-heading { grid-template-columns:40px minmax(0,1fr); background:#eaf5f8; border-bottom-color:#bfdbe4; }
    .notice-heading-icon { display:grid; place-items:center; width:40px; height:40px; border-radius:50%; color:#fff; background:#b7790b; font-size:1.05rem; }
    .outgoing-heading .notice-heading-icon { background:#0b5f78; }
    .notice-heading strong, .notice-heading small { display:block; }
    .notice-heading strong { color:#5b3a00; font-size:.96rem; font-weight:800; }
    .outgoing-heading strong { color:#003c4d; }
    .notice-heading small { margin-top:.08rem; color:#765a25; font-size:.8rem; font-weight:700; }
    .outgoing-heading small { color:#416b78; }
    .btn-receive-now { display:inline-flex; align-items:center; gap:.4rem; min-height:40px; color:#fff; background:#187246; border-color:#187246; font-weight:800; border-radius:4px; }
    .btn-receive-now:hover, .btn-receive-now:focus-visible { color:#fff; background:#115c37; border-color:#115c37; }
    .notice-list { display:grid; }
    .notice-row { display:grid; grid-template-columns:36px minmax(0,1.35fr) minmax(105px,.75fr) minmax(115px,.8fr) 16px; align-items:center; gap:.65rem; padding:.65rem .85rem; color:inherit; border-bottom:1px solid #edf0f3; text-decoration:none; transition:background-color .12s ease; }
    .notice-row:last-child { border-bottom:0; }
    .notice-row:hover, .notice-row:focus-visible { color:inherit; background:#f8fafc; }
    .row-arrow { color:#94a3b8; font-size:.8rem; }
    .person-avatar { display:grid; place-items:center; width:36px; height:36px; border-radius:50%; }
    .incoming-avatar { color:#187246; background:#e8f5ed; }
    .outgoing-avatar { color:#0b5f78; background:#e7f3f7; }
    .person-copy, .batch-copy, .waiting-copy { min-width:0; }
    .person-copy strong, .person-copy span, .batch-copy strong, .batch-copy span, .waiting-copy strong, .waiting-copy span { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .person-copy strong { color:#00284d; font-size:.88rem; font-weight:800; }
    .person-copy span, .batch-copy span, .waiting-copy span { color:#6b7280; font-size:.72rem; font-weight:600; }
    .batch-copy strong { font-size:.82rem; font-weight:800; }
    .incoming-batch strong { color:#187246; }
    .outgoing-batch strong { color:#0b5f78; }
    .waiting-copy { text-align:right; }
    .waiting-copy strong { color:#8a5a12; font-size:.8rem; font-weight:800; }
    .more-batches { display:flex; align-items:center; justify-content:center; gap:.4rem; padding:.55rem 1rem; color:#765a25; background:#fffaf0; border-top:1px solid #ead9a7; font-size:.78rem; font-weight:800; text-align:center; text-decoration:none; }
    .more-batches:hover, .more-batches:focus-visible { color:#5b3a00; background:#fff3d6; }
    .outgoing-more { color:#416b78; background:#f1f8fa; border-top-color:#bfdbe4; }
    .notice-empty { display:grid; place-items:center; min-height:180px; padding:1.5rem; text-align:center; background:#fff; border:1px solid #dbe3ec; border-radius:4px; }
    .notice-empty i { color:#187246; font-size:2rem; }
    .notice-empty strong { color:#00284d; font-weight:800; }
    .notice-empty span { color:#6b7280; font-size:.84rem; }
    .action-grid { display:grid; grid-template-columns:1fr; gap:1rem; }
    .desk-action {
        position: relative;
        display: grid;
        grid-template-columns: 64px minmax(0, 1fr) auto 22px;
        align-items: center;
        gap: 1rem;
        min-height: 142px;
        padding: 1.25rem;
        color: #fff;
        text-decoration: none;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 40, 77, .13);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .desk-action:hover, .desk-action:focus-visible { color: #fff; transform: translateY(-2px); box-shadow: 0 8px 18px rgba(0, 40, 77, .2); }
    .send-action { background: #0b5f78; border-left: 6px solid #d4a017; }
    .receive-action { background: #187246; border-left: 6px solid #efb929; }
    .action-icon { display: grid; place-items: center; width: 64px; height: 64px; border: 1px solid rgba(255,255,255,.35); border-radius: 50%; background: rgba(255,255,255,.12); font-size: 1.8rem; }
    .action-copy { min-width: 0; }
    .action-copy strong { display: block; font-size: 1.35rem; font-weight: 800; }
    .action-copy span { display: block; margin-top: .25rem; color: rgba(255,255,255,.85); font-size: .9rem; font-weight: 700; }
    .action-count { align-self: start; padding: .28rem .5rem; border-radius: 4px; background: rgba(255,255,255,.16); font-size: .76rem; font-weight: 800; white-space: nowrap; }
    .action-arrow { font-size: 1.2rem; }
    .custody-register { margin-top:1rem; overflow:hidden; background:#fff; border:1px solid #dbe3ec; border-top:3px solid #0b5f78; border-radius:4px; box-shadow:0 2px 8px rgba(0,40,77,.08); }
    .custody-heading { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.8rem 1rem; border-bottom:1px solid #e5eaf0; }
    .custody-heading h5 { margin:0; color:#00284d; font-size:1.05rem; font-weight:800; }
    .custody-total { padding:.3rem .6rem; color:#075b46; background:#e7f5ef; border:1px solid #b9dfcf; border-radius:4px; font-size:.8rem; font-weight:800; white-space:nowrap; }
    .custody-search { padding:.8rem 1rem; background:#f7f9fb; border-bottom:1px solid #e5eaf0; }
    .custody-search .input-group > * { min-height:46px; }
    .custody-search .input-group-text { color:#52606d; background:#fff; }
    .custody-search .form-control:focus { border-color:#d4a017; box-shadow:0 0 0 .15rem rgba(212,160,23,.15); }
    .btn-custody-search { padding-inline:1.25rem; color:#fff; background:#00284d; border-color:#00284d; font-weight:800; }
    .btn-custody-search:hover, .btn-custody-search:focus-visible { color:#fff; background:#001e3a; border-color:#001e3a; }
    .custody-result-line { padding:.45rem 1rem; color:#52606d; background:#fffdf5; border-bottom:1px solid #eee2bd; font-size:.78rem; font-weight:700; }
    .custody-list { display:grid; }
    .custody-row { display:grid; grid-template-columns:40px minmax(150px,.8fr) minmax(220px,1.35fr) minmax(145px,.75fr); align-items:center; gap:.8rem; min-height:76px; padding:.65rem 1rem; border-bottom:1px solid #edf0f3; }
    .custody-row:last-child { border-bottom:0; }
    .custody-row:hover { background:#f8fafc; }
    .custody-case-icon { display:grid; place-items:center; width:40px; height:40px; color:#0b5f78; background:#e7f3f7; border-radius:4px; font-size:1.15rem; }
    .custody-case-main, .custody-parties, .custody-meta { min-width:0; }
    .custody-case-main strong, .custody-case-main span, .custody-parties strong, .custody-parties span, .custody-meta strong, .custody-meta span { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .custody-case-main strong { color:#00284d; font-size:.93rem; font-weight:800; }
    .custody-case-main span, .custody-parties span, .custody-meta span { margin-top:.12rem; color:#6b7280; font-size:.75rem; font-weight:600; }
    .custody-parties strong { color:#374151; font-size:.84rem; font-weight:750; }
    .custody-meta { text-align:right; }
    .custody-meta strong { color:#187246; font-size:.8rem; font-weight:800; }
    .custody-meta .pending-custody { color:#9a6700; }
    .custody-empty { display:grid; justify-items:center; gap:.35rem; padding:2rem 1rem; color:#6b7280; text-align:center; }
    .custody-empty i { color:#94a3b8; font-size:1.8rem; }
    .custody-empty strong { font-size:.9rem; }
    .custody-pagination { display:flex; justify-content:flex-end; padding:.75rem 1rem; background:#f8fafc; border-top:1px solid #e5eaf0; }
    .custody-pagination nav { max-width:100%; }
    .custody-pagination svg { width:1rem; height:1rem; }
    @media (max-width: 767.98px) {
        .handover-home { padding-top: 1rem !important; }
        .desk-layout { grid-template-columns:1fr; }
        .notification-column { grid-row:2; }
        .notice-heading { grid-template-columns:38px minmax(0,1fr); }
        .notice-heading .btn { grid-column:1/-1; justify-content:center; }
        .notice-row { grid-template-columns:34px minmax(0,1fr) auto; }
        .row-arrow { display:none; }
        .person-avatar { width:34px; height:34px; }
        .batch-copy { text-align:right; }
        .waiting-copy { grid-column:2/-1; text-align:left; padding-top:.35rem; border-top:1px dashed #e5e7eb; }
        .desk-action { min-height: 128px; grid-template-columns: 54px minmax(0, 1fr) 20px; padding: 1rem; }
        .action-icon { width: 54px; height: 54px; }
        .action-count { position: absolute; right: 1rem; top: .75rem; }
        .custody-heading { align-items:flex-start; }
        .custody-search .input-group { flex-wrap:wrap; }
        .custody-search .input-group-text { display:none; }
        .custody-search .form-control { width:100%; border-radius:4px !important; }
        .custody-search .btn { margin-top:.45rem; border-radius:4px !important; }
        .custody-row { grid-template-columns:40px minmax(0,1fr); gap:.55rem .7rem; }
        .custody-parties, .custody-meta { grid-column:2; text-align:left; }
        .custody-parties { padding-top:.35rem; border-top:1px dashed #e5e7eb; }
        .custody-case-main strong, .custody-case-main span, .custody-parties strong, .custody-parties span, .custody-meta strong, .custody-meta span { white-space:normal; }
        .custody-pagination { justify-content:center; overflow-x:auto; }
    }
</style>
@endpush
