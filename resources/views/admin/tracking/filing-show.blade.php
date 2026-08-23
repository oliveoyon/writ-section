@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 filing-show-page">
    <div class="filing-header mb-3">
        <div>
            <div class="system-mark">RTFTS Filing</div>
            <h4 class="mb-0">{{ __('tracking.filing.details_title') }}</h4>
            <small>{{ $case->case_reference ?? '-' }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tracking.filing.print-label', ['case' => $case->id, 'auto' => 1]) }}" class="btn btn-gold btn-sm">
                <i class="bi bi-printer"></i>
                {{ __('tracking.filing.print_now') }}
            </a>
            <a href="{{ route('admin.tracking.filing.index') }}" class="btn btn-outline-brand btn-sm">{{ __('tracking.filing.back_to_module') }}</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="summary-grid mb-3">
        <div class="info-box"><span>{{ __('tracking.filing.case_no') }}</span><strong>{{ $case->case_reference ?? '-' }}</strong></div>
        <div class="info-box"><span>{{ __('tracking.lookup.permanent_barcode') }}</span><strong>{{ $case->permanent_barcode ?? '-' }}</strong></div>
        <div class="info-box"><span>{{ __('tracking.lookup.current_section') }}</span><strong>{{ $case->current_section ?? '-' }}</strong></div>
        <div class="info-box"><span>{{ __('tracking.filing.lawyer_name') }}</span><strong>{{ $case->lawyer?->full_name ?? '-' }}</strong></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="admin-panel h-100">
                <div class="panel-heading"><h5>{{ __('tracking.filing.petitioners') }}</h5></div>
                <ul class="party-list">
                    @forelse($case->petitioners as $p)
                        <li>
                            <strong>{{ $p->name_or_organization }}</strong>
                            @if($p->represented_by)<span>{{ $p->represented_by }}</span>@endif
                            @if($p->designation)<span>{{ $p->designation }}</span>@endif
                            @if($p->address)<small>{{ $p->address }}</small>@endif
                        </li>
                    @empty
                        <li class="text-muted">{{ __('tracking.filing.no_data') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="admin-panel h-100">
                <div class="panel-heading"><h5>{{ __('tracking.filing.respondents') }}</h5></div>
                <ul class="party-list">
                    @forelse($case->respondents as $r)
                        <li>
                            <strong>{{ $r->name_or_organization }}</strong>
                            @if($r->represented_by)<span>{{ $r->represented_by }}</span>@endif
                            @if($r->designation)<span>{{ $r->designation }}</span>@endif
                            @if($r->address)<small>{{ $r->address }}</small>@endif
                        </li>
                    @empty
                        <li class="text-muted">{{ __('tracking.filing.no_data') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="admin-panel">
        <div class="panel-heading"><h5>{{ __('tracking.timeline.history') }}</h5></div>
        <div class="table-responsive">
            <table class="table filing-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('tracking.timeline.time') }}</th>
                        <th>{{ __('tracking.timeline.from') }}</th>
                        <th>{{ __('tracking.timeline.to') }}</th>
                        <th>{{ __('tracking.timeline.type') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($case->movements as $move)
                        <tr>
                            <td>{{ optional($move->received_at)->format('d-m-Y h:i A') }}</td>
                            <td><span class="holder-badge">{{ $move->from_section ?? '-' }}</span></td>
                            <td><span class="holder-badge">{{ $move->to_section }}</span></td>
                            <td>{{ ucwords(str_replace('_', ' ', $move->movement_type)) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted text-center">{{ __('tracking.filing.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .filing-show-page { max-width: 1120px; }
    .filing-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .85rem 1rem; background: #fff; border: 1px solid #e3e8ef; border-top: 3px solid #00284d; border-bottom-color: #d4a017; border-radius: 4px; box-shadow: 0 1px 5px rgba(0, 40, 77, .08); }
    .filing-header h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
    .filing-header small { color: #6b7280; font-weight: 600; }
    .system-mark { color: #b87d08; font-size: .82rem; font-weight: 800; }
    .summary-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem; }
    .info-box { background: #fff; border: 1px solid #e3e8ef; border-left: 4px solid #d4a017; border-radius: 4px; padding: .75rem; box-shadow: 0 1px 5px rgba(0, 40, 77, .06); }
    .info-box span { display: block; color: #6b7280; font-size: .76rem; font-weight: 800; text-transform: uppercase; }
    .info-box strong { display: block; color: #111827; margin-top: .25rem; word-break: break-word; }
    .admin-panel { background: #fff; border: 1px solid #e3e8ef; border-radius: 4px; box-shadow: 0 1px 5px rgba(0, 40, 77, .07); overflow: hidden; }
    .panel-heading { padding: .75rem 1rem; background: #fbfcfe; border-top: 3px solid #00284d; border-bottom: 1px solid #e5e7eb; }
    .panel-heading h5 { margin: 0; color: #1f2937; font-size: 1rem; font-weight: 800; }
    .party-list { list-style: none; margin: 0; padding: .85rem 1rem; }
    .party-list li { padding: .6rem 0; border-bottom: 1px solid #eef2f7; }
    .party-list li:last-child { border-bottom: 0; }
    .party-list strong, .party-list span, .party-list small { display: block; }
    .party-list span { color: #4b5563; }
    .party-list small { color: #6b7280; }
    .filing-table { font-size: .9rem; }
    .filing-table thead th { background: #eef5fb; color: #00284d; font-weight: 800; border-bottom: 0; white-space: nowrap; }
    .holder-badge { display: inline-flex; border-radius: 4px; padding: .2rem .5rem; font-size: .78rem; font-weight: 800; border: 1px solid #d8e3ef; background: #f7fbff; color: #0b4f8a; }
    .btn-outline-brand { color: #00284d; border-color: #00284d; }
    .btn-outline-brand:hover { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-gold { background: #d4a017; color: #111827; border-color: #d4a017; font-weight: 800; }
    .btn-gold:hover { background: #b38b0f; color: #fff; border-color: #b38b0f; }
    @media (max-width: 767.98px) {
        .filing-header { align-items: stretch; flex-direction: column; }
        .filing-header .d-flex { display: grid !important; grid-template-columns: 1fr; }
        .summary-grid { grid-template-columns: 1fr; }
        .filing-table { min-width: 720px; }
    }
</style>
@endpush
