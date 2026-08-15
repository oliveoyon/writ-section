@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 filing-home-page">
    <div class="filing-toolbar d-flex align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-folder-check filing-heading-icon" aria-hidden="true"></i>
            <h3 class="filing-heading mb-0">{{ auth()->user()->name }}: {{ __('tracking.filing.module_title') }}</h3>
        </div>
        <div class="filing-actions d-flex gap-2">
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

    <div class="pending-strip d-flex align-items-center justify-content-between mb-4">
        <span>{{ __('tracking.filing.pending_temp') }}</span>
        <strong>{{ $pendingTempCount }}</strong>
    </div>

    <section class="recent-files">
        <h5 class="mb-3">{{ __('tracking.filing.recent_files') }}</h5>
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
                            <td>{{ $item->currentHolder?->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('admin.tracking.filing.show', $item) }}" class="btn btn-sm btn-outline-brand" title="{{ __('tracking.filing.view') }}">
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
    .filing-heading { font-size: 1.35rem; font-weight: 650; color: #1f2937; }
    .filing-heading-icon { color: #0f766e; font-size: 1.45rem; }
    .kiosk-action { min-height: 46px; display: inline-flex; align-items: center; gap: .45rem; color: #fff; font-weight: 600; }
    .kiosk-action:hover, .kiosk-action:focus-visible { color: #fff; }
    .btn-scan { background: #0f766e; border-color: #0f766e; }
    .btn-new { background: #b45309; border-color: #b45309; }
    .btn-print { background: #2563eb; border-color: #2563eb; }
    .btn-scan:hover, .btn-scan:focus-visible { background: #0b5f59; border-color: #0b5f59; }
    .btn-new:hover, .btn-new:focus-visible { background: #92400e; border-color: #92400e; }
    .btn-print:hover, .btn-print:focus-visible { background: #1d4ed8; border-color: #1d4ed8; }
    .pending-strip { padding: .8rem 1rem; border-left: 4px solid #d4a017; background: #f7f8fa; color: #4b5563; }
    .pending-strip strong { min-width: 36px; font-size: 1.35rem; color: #111827; text-align: center; }
    .recent-files { border-top: 1px solid #e5e7eb; padding-top: 1.25rem; }
    .recent-files h5 { font-size: 1rem; font-weight: 650; }
    .filing-table { font-size: .9rem; }
    .filing-table thead th { background: #f7f8fa; color: #6b7280; font-size: .75rem; text-transform: uppercase; border-bottom-width: 1px; }
    .filing-table td, .filing-table th { padding: .75rem; border-color: #edf0f2; }
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-gold { background: #d4a017; color: #fff; border-color: #d4a017; }
    .btn-gold:hover { background: #b38b0f; color: #fff; border-color: #b38b0f; }
    .btn-outline-brand { color: #00284d; border-color: #00284d; }
    .btn-outline-brand:hover { background: #00284d; color: #fff; border-color: #00284d; }
    @media (max-width: 767.98px) {
        .filing-home-page { padding-top: 1rem !important; }
        .filing-toolbar { align-items: stretch !important; flex-direction: column; }
        .filing-actions { display: grid !important; grid-template-columns: 1fr 1fr; }
        .kiosk-action { justify-content: center; }
        .btn-print { grid-column: 1 / -1; }
        .filing-table { min-width: 760px; }
    }
</style>
@endpush
