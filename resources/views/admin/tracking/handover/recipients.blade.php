@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 recipient-page">
    <div class="page-header mb-3">
        <div>
            <div class="system-mark">RTFTS File Desk</div>
            <h4 class="mb-0">Send Files</h4>
            <small>Select the person who will receive the files</small>
        </div>
        <a href="{{ route('admin.tracking.handover.workspace') }}" class="btn btn-light btn-sm border">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> File Desk
        </a>
    </div>

    <form method="GET" class="recipient-search mb-3" role="search">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search" aria-hidden="true"></i></span>
            <input type="search" name="q" class="form-control form-control-lg" value="{{ $search }}" placeholder="Name, Employee ID or department" autofocus>
            <button class="btn btn-brand px-4" type="submit">Search</button>
            @if($search !== '')
                <a class="btn btn-outline-secondary" href="{{ route('admin.tracking.handover.recipients') }}" title="Clear search"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>

    <div class="recipient-grid">
        @forelse($recipients as $recipient)
            <a class="recipient-card" href="{{ route('admin.tracking.handover.create', $recipient) }}">
                <span class="recipient-icon"><i class="bi bi-person" aria-hidden="true"></i></span>
                <span class="recipient-details">
                    <strong>{{ $recipient->name }}</strong>
                    <span>{{ $recipient->departmentRelation?->label ?? 'Unassigned' }}</span>
                    <small>Employee ID: {{ $recipient->employee_id ?: '-' }}</small>
                </span>
                <i class="bi bi-chevron-right recipient-arrow" aria-hidden="true"></i>
            </a>
        @empty
            <div class="empty-result">No active staff user found.</div>
        @endforelse
    </div>

    @if($recipients->hasPages())
        <div class="pagination-wrap">{{ $recipients->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection

@push('css')
<style>
    .recipient-page { max-width: 1040px; }
    .page-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.85rem 1rem; background:#fff; border:1px solid #e3e8ef; border-top:3px solid #00284d; border-bottom-color:#d4a017; border-radius:4px; box-shadow:0 1px 5px rgba(0,40,77,.08); }
    .page-header h4 { color:#00284d; font-size:1.15rem; font-weight:800; }
    .page-header small { color:#6b7280; font-weight:600; }
    .system-mark { color:#b87d08; font-size:.82rem; font-weight:800; }
    .recipient-search { padding:1rem; background:#fff; border:1px solid #e3e8ef; border-radius:4px; }
    .recipient-search .input-group > * { min-height:50px; }
    .recipient-search .form-control:focus { border-color:#d4a017; box-shadow:0 0 0 .15rem rgba(212,160,23,.15); }
    .btn-brand { background:#00284d; color:#fff; border-color:#00284d; font-weight:800; }
    .btn-brand:hover { background:#001e3a; color:#fff; border-color:#001e3a; }
    .recipient-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.75rem; }
    .recipient-card { display:grid; grid-template-columns:44px minmax(0,1fr) 18px; align-items:center; gap:.75rem; min-height:104px; padding:.85rem; color:#1f2937; text-decoration:none; background:#fff; border:1px solid #dbe3ec; border-left:4px solid #0b5f78; border-radius:4px; box-shadow:0 1px 4px rgba(0,40,77,.06); }
    .recipient-card:hover, .recipient-card:focus-visible { color:#1f2937; border-color:#d4a017; box-shadow:0 4px 10px rgba(0,40,77,.12); }
    .recipient-icon { display:grid; place-items:center; width:44px; height:44px; border-radius:50%; color:#fff; background:#0b5f78; font-size:1.25rem; }
    .recipient-details { min-width:0; }
    .recipient-details strong, .recipient-details span, .recipient-details small { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .recipient-details strong { color:#00284d; font-weight:800; }
    .recipient-details span { margin-top:.18rem; color:#52606d; font-size:.84rem; font-weight:700; }
    .recipient-details small { margin-top:.12rem; color:#77808b; font-size:.76rem; }
    .recipient-arrow { color:#0b5f78; }
    .empty-result { grid-column:1/-1; padding:2rem; text-align:center; background:#fff; border:1px solid #e3e8ef; color:#6b7280; }
    .pagination-wrap { display:flex; justify-content:flex-end; padding-top:1rem; }
    .pagination-wrap svg { width:1rem; height:1rem; }
    @media (max-width:991.98px) { .recipient-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:575.98px) { .recipient-page { padding-top:1rem !important; } .page-header { align-items:stretch; flex-direction:column; } .recipient-grid { grid-template-columns:1fr; } .recipient-search .input-group { flex-wrap:wrap; } .recipient-search .input-group-text { display:none; } .recipient-search .form-control { width:100%; border-radius:4px !important; } .recipient-search .btn { margin-top:.5rem; border-radius:4px !important; } }
</style>
@endpush
