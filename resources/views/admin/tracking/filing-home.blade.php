@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 filing-home-page">
    <div class="filing-header mb-3">
        <div>
            <div class="system-mark">RTFTS Filing</div>
            <h4 class="mb-0">{{ auth()->user()->name }}: {{ __('tracking.filing.module_title') }}</h4>
            <small>{{ $section }}</small>
        </div>
        <div class="filing-actions">
            <a class="btn kiosk-action btn-scan" href="{{ route('admin.tracking.filing.scan-temp') }}">
                <i class="bi bi-upc-scan" aria-hidden="true"></i><span>{{ __('tracking.filing.scan_title') }}</span>
            </a>
            <a class="btn kiosk-action btn-new" href="{{ route('admin.tracking.filing.direct-create') }}">
                <i class="bi bi-file-earmark-plus" aria-hidden="true"></i><span>{{ __('tracking.filing.direct_title') }}</span>
            </a>
            <a class="btn kiosk-action btn-print" href="{{ route('admin.tracking.filing.print-index') }}">
                <i class="bi bi-printer" aria-hidden="true"></i><span>{{ __('tracking.filing.print_module_title') }}</span>
            </a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="summary-box">
                <i class="bi bi-clock-history"></i>
                <span>{{ __('tracking.filing.pending_temp') }}</span>
                <strong>{{ $pendingTempCount }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-box ready">
                <i class="bi bi-folder-check"></i>
                <span>{{ __('tracking.filing.recent_files') }}</span>
                <strong>{{ $recentCases->count() }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-box section">
                <i class="bi bi-person-badge"></i>
                <span>{{ __('tracking.filing.current_holder') }}</span>
                <strong>{{ auth()->user()->name }}</strong>
            </div>
        </div>
    </div>

    <section class="admin-panel recent-files">
        <div class="panel-heading">
            <div>
                <h5>{{ __('tracking.filing.recent_files') }}</h5>
                <span>{{ __('tracking.filing.case_no') }} and current custody</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table filing-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('tracking.filing.case_no') }}</th>
                        <th>{{ __('tracking.filing.lawyer_name') }}</th>
                        <th>{{ __('tracking.filing.current_holder') }}</th>
                        <th>{{ __('tracking.filing.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentCases as $item)
                        <tr>
                            <td>{{ $item->case_reference ?? '-' }}</td>
                            <td>{{ $item->lawyer?->full_name ?? '-' }}</td>
                            <td><span class="holder-badge">{{ $item->currentHolder?->name ?? '-' }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.tracking.filing.show', $item) }}" class="btn btn-action" title="{{ __('tracking.filing.view') }}">
                                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                    <span class="visually-hidden">{{ __('tracking.filing.view') }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">{{ __('tracking.filing.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('css')
<style>
    .filing-home-page { max-width: 1120px; }
    .filing-header {
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
    .filing-header h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
    .filing-header small { color: #6b7280; font-weight: 600; }
    .system-mark { color: #b87d08; font-size: .82rem; font-weight: 800; }
    .filing-actions { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: flex-end; }
    .kiosk-action { min-height: 38px; display: inline-flex; align-items: center; gap: .45rem; color: #fff; font-weight: 800; border-radius: 4px; }
    .kiosk-action:hover, .kiosk-action:focus-visible { color: #fff; }
    .btn-scan { background: #0f766e; border-color: #0f766e; }
    .btn-new { background: #b45309; border-color: #b45309; }
    .btn-print { background: #2563eb; border-color: #2563eb; }
    .btn-scan:hover, .btn-scan:focus-visible { background: #0b5f59; border-color: #0b5f59; }
    .btn-new:hover, .btn-new:focus-visible { background: #92400e; border-color: #92400e; }
    .btn-print:hover, .btn-print:focus-visible { background: #1d4ed8; border-color: #1d4ed8; }
    .summary-box { position: relative; min-height: 94px; padding: .85rem .9rem; background: #fff; border: 1px solid #e3e8ef; border-left: 4px solid #d4a017; border-radius: 4px; box-shadow: 0 1px 5px rgba(0, 40, 77, .06); }
    .summary-box.ready { border-left-color: #0f766e; }
    .summary-box.section { border-left-color: #2563eb; }
    .summary-box i { position: absolute; right: .85rem; top: .85rem; color: #d4a017; font-size: 1.35rem; }
    .summary-box span { display: block; color: #6b7280; font-size: .82rem; font-weight: 800; text-transform: uppercase; }
    .summary-box strong { display: block; color: #111827; font-size: 1.6rem; line-height: 1.1; margin-top: .45rem; word-break: break-word; }
    .admin-panel { background: #fff; border: 1px solid #e3e8ef; border-radius: 4px; box-shadow: 0 1px 5px rgba(0, 40, 77, .07); overflow: hidden; }
    .panel-heading { padding: .75rem 1rem; background: #fbfcfe; border-top: 3px solid #00284d; border-bottom: 1px solid #e5e7eb; }
    .panel-heading h5 { margin: 0; color: #1f2937; font-size: 1rem; font-weight: 800; }
    .panel-heading span { color: #6b7280; font-size: .84rem; font-weight: 600; }
    .filing-table { font-size: .9rem; }
    .filing-table thead th { background: #eef5fb; color: #00284d; font-weight: 800; border-bottom: 0; white-space: nowrap; }
    .filing-table td, .filing-table th { padding: .75rem; border-color: #edf0f2; }
    .holder-badge { display: inline-flex; border-radius: 4px; padding: .2rem .5rem; font-size: .78rem; font-weight: 800; border: 1px solid #d8e3ef; background: #f7fbff; color: #0b4f8a; }
    .btn-action { width: 2rem; height: 2rem; display: inline-grid; place-items: center; padding: 0; border-radius: 4px; border: 1px solid #bcd0e2; color: #0b4f8a; background: #f2f7fc; }
    .btn-action:hover { background: #0b4f8a; color: #fff; }
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-gold { background: #d4a017; color: #fff; border-color: #d4a017; }
    .btn-gold:hover { background: #b38b0f; color: #fff; border-color: #b38b0f; }
    .btn-outline-brand { color: #00284d; border-color: #00284d; }
    .btn-outline-brand:hover { background: #00284d; color: #fff; border-color: #00284d; }
    @media (max-width: 767.98px) {
        .filing-home-page { padding-top: 1rem !important; }
        .filing-header { align-items: stretch; flex-direction: column; }
        .filing-actions { display: grid !important; grid-template-columns: 1fr 1fr; }
        .kiosk-action { justify-content: center; }
        .btn-print { grid-column: 1 / -1; }
        .filing-table { min-width: 760px; }
    }
</style>
@endpush
