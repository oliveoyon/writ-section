@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 register-page">
    <div class="register-header mb-3">
        <div>
            <div class="system-mark">RTFTS Report</div>
            <h4 class="mb-0">{{ auth()->user()->name }}: {{ __('tracking.register.title') }}</h4>
            <small>{{ __('tracking.register.print_title') }}</small>
        </div>
        <div class="report-actions no-print">
            <a href="{{ route('admin.tracking.register-report.pdf', request()->query()) }}" target="_blank" class="btn btn-brand btn-sm">
                <i class="bi bi-file-earmark-pdf"></i> {{ __('tracking.register.pdf_button') }}
            </a>
            <button type="button" class="btn btn-gold btn-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> {{ __('tracking.register.print_button') }}
            </button>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.tracking.register-report') }}" class="filter-panel admin-panel no-print mb-3">
        <div class="panel-heading">
            <div>
                <h5>Filter Report</h5>
                <span>{{ \Illuminate\Support\Carbon::parse($dateFrom)->format('d-m-Y') }} - {{ \Illuminate\Support\Carbon::parse($dateTo)->format('d-m-Y') }}</span>
            </div>
        </div>
        <div class="panel-body">
        <div class="row g-3 align-items-end">
            <div class="col-sm-6 col-lg-4">
                <label for="filter_mode" class="form-label">{{ __('tracking.register.filter_mode') }}</label>
                <select id="filter_mode" name="filter_mode" class="form-select">
                    <option value="date_range" @selected($filterMode === 'date_range')>{{ __('tracking.register.mode_date_range') }}</option>
                    <option value="month" @selected($filterMode === 'month')>{{ __('tracking.register.mode_month') }}</option>
                    <option value="year" @selected($filterMode === 'year')>{{ __('tracking.register.mode_year') }}</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-4 mode-date-range">
                <label for="date_from" class="form-label">{{ __('tracking.register.date_from') }}</label>
                <div class="report-date-picker">
                    <input type="text" id="date_from" name="date_from" class="form-control" value="{{ \Illuminate\Support\Carbon::parse($dateFrom)->format('d-m-Y') }}" placeholder="DD-MM-YYYY" pattern="\d{2}-\d{2}-\d{4}" readonly required>
                    <input type="date" class="native-date-picker" data-date-target="date_from" value="{{ \Illuminate\Support\Carbon::parse($dateFrom)->format('Y-m-d') }}" tabindex="-1" aria-label="{{ __('tracking.register.date_from') }}">
                    <i class="bi bi-calendar3 date-picker-icon" aria-hidden="true"></i>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4 mode-date-range">
                <label for="date_to" class="form-label">{{ __('tracking.register.date_to') }}</label>
                <div class="report-date-picker">
                    <input type="text" id="date_to" name="date_to" class="form-control" value="{{ \Illuminate\Support\Carbon::parse($dateTo)->format('d-m-Y') }}" placeholder="DD-MM-YYYY" pattern="\d{2}-\d{2}-\d{4}" readonly required>
                    <input type="date" class="native-date-picker" data-date-target="date_to" value="{{ \Illuminate\Support\Carbon::parse($dateTo)->format('Y-m-d') }}" tabindex="-1" aria-label="{{ __('tracking.register.date_to') }}">
                    <i class="bi bi-calendar3 date-picker-icon" aria-hidden="true"></i>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4 mode-month">
                <label for="month" class="form-label">{{ __('tracking.register.month') }}</label>
                <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
            </div>
            <div class="col-sm-6 col-lg-4 mode-year">
                <label for="year" class="form-label">{{ __('tracking.register.year') }}</label>
                <input type="number" min="2000" max="2100" id="year" name="year" class="form-control" value="{{ $year }}">
            </div>
            <div class="col-sm-6 col-lg-4">
                <label for="section" class="form-label">{{ __('tracking.register.section') }}</label>
                <select id="section" name="section" class="form-select" {{ $canViewAllSections ? '' : 'disabled' }}>
                    <option value="">{{ __('tracking.register.all_sections') }}</option>
                    @foreach($sections as $item)
                        <option value="{{ $item }}" @selected($section === $item)>{{ $item }}</option>
                    @endforeach
                </select>
                @unless($canViewAllSections)
                    <input type="hidden" name="section" value="{{ $userSection }}">
                @endunless
            </div>
            <div class="col-sm-6 col-lg-4">
                <label for="movement_type" class="form-label">{{ __('tracking.register.movement_type') }}</label>
                <select id="movement_type" name="movement_type" class="form-select">
                    <option value="">{{ __('tracking.register.all_types') }}</option>
                    <option value="receive" @selected($movementType === 'receive')>{{ __('tracking.receive.receive') }}</option>
                    <option value="reject" @selected($movementType === 'reject')>{{ __('tracking.receive.reject') }}</option>
                    <option value="override_receive" @selected($movementType === 'override_receive')>{{ __('tracking.register.override_receive') }}</option>
                    <option value="dispatch_to_court" @selected($movementType === 'dispatch_to_court')>{{ __('tracking.register.dispatch_to_court') }}</option>
                    <option value="returned_from_court_handover" @selected($movementType === 'returned_from_court_handover')>{{ __('tracking.register.returned_from_court_handover') }}</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-4">
                <label for="movement_scope" class="form-label">{{ __('tracking.register.movement_scope') }}</label>
                <select id="movement_scope" name="movement_scope" class="form-select">
                    <option value="all" @selected($movementScope === 'all')>{{ __('tracking.register.scope_all_filter') }}</option>
                    <option value="in" @selected($movementScope === 'in')>{{ __('tracking.register.scope_in') }}</option>
                    <option value="out" @selected($movementScope === 'out')>{{ __('tracking.register.scope_out') }}</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-4">
                <button type="submit" class="btn btn-brand filter-button">
                    <i class="bi bi-funnel" aria-hidden="true"></i> {{ __('tracking.register.filter') }}
                </button>
            </div>
        </div>
        </div>
    </form>

    <section class="report-area admin-panel" id="printArea">
        @php
            $scopeLabelKey = match ($movementScope) {
                'in' => 'tracking.register.scope_in',
                'out' => 'tracking.register.scope_out',
                default => 'tracking.register.scope_all_filter',
            };
            $movementTypeLabelKeys = [
                'receive' => 'tracking.receive.receive',
                'reject' => 'tracking.receive.reject',
                'override_receive' => 'tracking.register.override_receive',
                'dispatch_to_court' => 'tracking.register.dispatch_to_court',
                'returned_from_court_handover' => 'tracking.register.returned_from_court_handover',
            ];
        @endphp
        <div class="report-summary">
            <div>
                <h5 class="mb-1">{{ __('tracking.register.print_title') }}</h5>
                <div class="summary-meta">
                    <span><i class="bi bi-calendar3"></i>{{ \Illuminate\Support\Carbon::parse($dateFrom)->format('d-m-Y') }} - {{ \Illuminate\Support\Carbon::parse($dateTo)->format('d-m-Y') }}</span>
                    <span><i class="bi bi-arrow-left-right"></i>{{ __($scopeLabelKey) }}</span>
            @if($section !== '')
                    <span><i class="bi bi-diagram-3"></i>{{ $section }}</span>
            @endif
                </div>
            </div>
            <div class="total-box">
                <span>{{ __('tracking.register.total') }}</span>
                <strong>{{ $movements->count() }}</strong>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table register-table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('tracking.register.time') }}</th>
                        <th>{{ __('tracking.register.case_no') }}</th>
                        {{-- <th>{{ __('tracking.register.barcode') }}</th> --}}
                        <th>{{ __('tracking.register.from') }}</th>
                        <th>{{ __('tracking.register.to') }}</th>
                        <th>{{ __('tracking.register.movement_type') }}</th>
                        <th>{{ __('tracking.register.by') }}</th>
                        <th>{{ __('tracking.register.notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $i => $movement)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ optional($movement->received_at)->format('d-m-Y h:i A') }}</td>
                            <td>{{ $movement->courtCase?->case_reference ?? ('CASE-' . ($movement->case_id ?? '')) }}</td>
                            {{-- <td>{{ $movement->barcode_scanned }}</td> --}}
                            <td><span class="section-badge">{{ $movement->from_section ?? '-' }}</span></td>
                            <td><span class="section-badge">{{ $movement->to_section ?? '-' }}</span></td>
                            <td><span class="type-badge">{{ isset($movementTypeLabelKeys[$movement->movement_type]) ? __($movementTypeLabelKeys[$movement->movement_type]) : $movement->movement_type }}</span></td>
                            <td>{{ $movement->receivedBy?->name ?? '-' }}</td>
                            <td>{{ $movement->notes ?: ($movement->override_reason ?: '-') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">{{ __('tracking.register.no_data') }}</td>
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
    .register-page { max-width: 1140px; }
    .register-header {
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
    .register-header h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
    .register-header small { color: #6b7280; font-weight: 600; }
    .system-mark { color: #b87d08; font-size: .82rem; font-weight: 800; }
    .report-actions { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: flex-end; }
    .admin-panel {
        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: 4px;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .07);
        overflow: hidden;
    }
    .panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .75rem 1rem;
        background: #fbfcfe;
        border-top: 3px solid #00284d;
        border-bottom: 1px solid #e5e7eb;
    }
    .panel-heading h5 { margin: 0; color: #1f2937; font-size: 1rem; font-weight: 800; }
    .panel-heading span { color: #6b7280; font-size: .84rem; font-weight: 600; }
    .panel-body { padding: 1rem; }
    .filter-panel .form-label { margin-bottom: .3rem; color: #374151; font-size: .8rem; font-weight: 800; }
    .filter-panel .form-control, .filter-panel .form-select, .filter-button { min-height: 42px; }
    .report-date-picker { position: relative; border-radius: 5px; }
    .report-date-picker .form-control { padding-right: 46px; border-color: #94a3b8; background: #f8fafc; cursor: pointer; }
    .report-date-picker:focus-within .form-control { border-color: #0f766e; box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .14); }
    .native-date-picker { position: absolute; inset: 0; z-index: 2; width: 100%; height: 100%; cursor: pointer; opacity: 0; }
    .date-picker-icon { position: absolute; z-index: 1; top: 1px; right: 1px; bottom: 1px; width: 40px; display: grid; place-items: center; border-left: 1px solid #cbd5e1; border-radius: 0 4px 4px 0; background: #e2e8f0; color: #334155; pointer-events: none; }
    .filter-button { width: 100%; }
    .report-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        background: #fbfcfe;
        border-top: 3px solid #00284d;
        border-bottom: 1px solid #e5e7eb;
    }
    .report-summary h5 { color: #1f2937; font-size: 1rem; font-weight: 800; }
    .summary-meta { display: flex; flex-wrap: wrap; gap: .5rem; }
    .summary-meta span {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid #d8e3ef;
        background: #f7fbff;
        color: #0b4f8a;
        border-radius: 4px;
        padding: .22rem .55rem;
        font-size: .8rem;
        font-weight: 800;
    }
    .total-box { min-width: 94px; text-align: center; border-left: 4px solid #d4a017; background: #fff; padding: .45rem .7rem; }
    .total-box span { display: block; color: #6b7280; font-size: .72rem; font-weight: 800; text-transform: uppercase; }
    .total-box strong { color: #111827; font-size: 1.35rem; line-height: 1; }
    .register-table thead th { background: #eef5fb; color: #00284d; border-bottom: 0; font-weight: 800; white-space: nowrap; }
    .register-table td, .register-table th { padding: .6rem .7rem; }
    .section-badge, .type-badge {
        display: inline-flex;
        border-radius: 4px;
        border: 1px solid #d8e3ef;
        background: #f7fbff;
        color: #0b4f8a;
        font-size: .76rem;
        font-weight: 800;
        padding: .18rem .45rem;
        white-space: nowrap;
    }
    .type-badge { background: #fff7e6; border-color: #f3d58e; color: #805500; }
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-gold { background: #d4a017; color: #1f2933; border-color: #d4a017; font-weight: 800; }
    .btn-gold:hover { background: #bc8d12; color: #fff; border-color: #bc8d12; }
    .mode-month, .mode-year { display: none; }
    @media print {
        .navbar, footer, .no-print { display: none !important; }
        main { padding-top: 0 !important; }
        .report-area { border: 0 !important; box-shadow: none !important; }
        .report-summary { border-top: 0; }
        .section-badge, .type-badge { border: 0; background: transparent; color: #000; padding: 0; }
    }
    @media (max-width: 767.98px) {
        .register-page { padding-top: 1rem !important; }
        .register-header { align-items: stretch; flex-direction: column; }
        .report-actions { display: grid; grid-template-columns: 1fr 1fr; }
        .report-summary { align-items: stretch; flex-direction: column; }
        .register-table { min-width: 980px; }
        .filter-button { width: 100%; }
    }
</style>
@endpush

@push('js')
<script>
    function applyFilterMode() {
        const mode = document.getElementById('filter_mode').value;
        document.querySelectorAll('.mode-date-range').forEach(el => el.style.display = mode === 'date_range' ? '' : 'none');
        document.querySelectorAll('.mode-month').forEach(el => el.style.display = mode === 'month' ? '' : 'none');
        document.querySelectorAll('.mode-year').forEach(el => el.style.display = mode === 'year' ? '' : 'none');

        document.getElementById('date_from').required = mode === 'date_range';
        document.getElementById('date_to').required = mode === 'date_range';
        document.getElementById('month').required = mode === 'month';
        document.getElementById('year').required = mode === 'year';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modeSelect = document.getElementById('filter_mode');
        modeSelect.addEventListener('change', applyFilterMode);

        document.querySelectorAll('.native-date-picker').forEach(picker => {
            picker.addEventListener('click', function () {
                if (typeof this.showPicker === 'function') {
                    try { this.showPicker(); } catch (error) {}
                }
            });
            picker.addEventListener('change', function () {
                const [year, month, day] = this.value.split('-');
                if (year && month && day) {
                    document.getElementById(this.dataset.dateTarget).value = `${day}-${month}-${year}`;
                }
            });
        });

        applyFilterMode();
    });
</script>
@endpush
