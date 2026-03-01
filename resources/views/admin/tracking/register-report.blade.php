@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h3 class="mb-1">{{ __('tracking.register.title') }}</h3>
            <p class="text-muted mb-0">{{ __('tracking.register.subtitle') }}</p>
            <p class="small mb-0 text-muted">
                {{ $canViewAllSections ? __('tracking.register.scope_all') : __('tracking.register.scope_own', ['section' => ($userSection ?: __('tracking.register.not_set'))]) }}
            </p>
        </div>
        <div class="d-flex gap-2 no-print">
            <a href="{{ route('admin.tracking.register-report.pdf', request()->query()) }}" target="_blank" class="btn btn-brand">
                <i class="bi bi-file-earmark-pdf"></i> {{ __('tracking.register.pdf_button') }}
            </a>
            <button type="button" class="btn btn-gold" onclick="window.print()">
                <i class="bi bi-printer"></i> {{ __('tracking.register.print_button') }}
            </button>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.tracking.register-report') }}" class="card p-3 mb-3 no-print">
        <div class="row g-3 mb-1">
            <div class="col-12">
                <label for="filter_mode" class="form-label">{{ __('tracking.register.filter_mode') }}</label>
                <select id="filter_mode" name="filter_mode" class="form-select">
                    <option value="date_range" @selected($filterMode === 'date_range')>{{ __('tracking.register.mode_date_range') }}</option>
                    <option value="month" @selected($filterMode === 'month')>{{ __('tracking.register.mode_month') }}</option>
                    <option value="year" @selected($filterMode === 'year')>{{ __('tracking.register.mode_year') }}</option>
                </select>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-3 mode-date-range">
                <label for="date_from" class="form-label">{{ __('tracking.register.date_from') }}</label>
                <input type="date" id="date_from" name="date_from" class="form-control" value="{{ $dateFrom }}" required>
            </div>
            <div class="col-md-3 mode-date-range">
                <label for="date_to" class="form-label">{{ __('tracking.register.date_to') }}</label>
                <input type="date" id="date_to" name="date_to" class="form-control" value="{{ $dateTo }}" required>
            </div>
            <div class="col-md-3 mode-month">
                <label for="month" class="form-label">{{ __('tracking.register.month') }}</label>
                <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
            </div>
            <div class="col-md-3 mode-year">
                <label for="year" class="form-label">{{ __('tracking.register.year') }}</label>
                <input type="number" min="2000" max="2100" id="year" name="year" class="form-control" value="{{ $year }}">
            </div>
            <div class="col-md-3">
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
            <div class="col-md-3">
                <label for="movement_type" class="form-label">{{ __('tracking.register.movement_type') }}</label>
                <select id="movement_type" name="movement_type" class="form-select">
                    <option value="">{{ __('tracking.register.all_types') }}</option>
                    <option value="receive" @selected($movementType === 'receive')>{{ __('tracking.receive.receive') }}</option>
                    <option value="reject" @selected($movementType === 'reject')>{{ __('tracking.receive.reject') }}</option>
                    <option value="override_receive" @selected($movementType === 'override_receive')>{{ __('tracking.register.override_receive') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="movement_scope" class="form-label">{{ __('tracking.register.movement_scope') }}</label>
                <select id="movement_scope" name="movement_scope" class="form-select">
                    <option value="all" @selected($movementScope === 'all')>{{ __('tracking.register.scope_all_filter') }}</option>
                    <option value="in" @selected($movementScope === 'in')>{{ __('tracking.register.scope_in') }}</option>
                    <option value="out" @selected($movementScope === 'out')>{{ __('tracking.register.scope_out') }}</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-brand mt-3">{{ __('tracking.register.filter') }}</button>
    </form>

    <div class="card p-3" id="printArea">
        @php
            $scopeLabelKey = match ($movementScope) {
                'in' => 'tracking.register.scope_in',
                'out' => 'tracking.register.scope_out',
                default => 'tracking.register.scope_all_filter',
            };
        @endphp
        <div class="text-center mb-3">
            <h5 class="mb-1">{{ __('tracking.register.print_title') }}</h5>
            <div class="small">{{ __('tracking.register.print_range') }}: {{ $dateFrom }} - {{ $dateTo }}</div>
            <div class="small">{{ __('tracking.register.filter_mode') }}: {{ __('tracking.register.mode_' . $filterMode) }}</div>
            <div class="small">{{ __('tracking.register.movement_scope') }}: {{ __($scopeLabelKey) }}</div>
            @if($section !== '')
                <div class="small">{{ __('tracking.register.section') }}: {{ $section }}</div>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
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
                            <td>{{ optional($movement->received_at)->format('Y-m-d h:i A') }}</td>
                            <td>{{ $movement->courtCase?->final_case_number ?? ('CASE-' . ($movement->case_id ?? '')) }}</td>
                            {{-- <td>{{ $movement->barcode_scanned }}</td> --}}
                            <td>{{ $movement->from_section ?? '-' }}</td>
                            <td>{{ $movement->to_section ?? '-' }}</td>
                            <td>{{ $movement->movement_type }}</td>
                            <td>{{ $movement->receivedBy?->name ?? '-' }}</td>
                            <td>{{ $movement->notes ?: ($movement->override_reason ?: '-') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">{{ __('tracking.register.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2 small">
            <strong>{{ __('tracking.register.total') }}:</strong> {{ $movements->count() }}
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-gold { background: #d4a017; color: #1f2933; border-color: #d4a017; }
    .btn-gold:hover { background: #bc8d12; color: #fff; border-color: #bc8d12; }
    .mode-month, .mode-year { display: none; }
    @media print {
        .navbar, footer, .no-print { display: none !important; }
        main { padding-top: 0 !important; }
        .card { border: 0 !important; box-shadow: none !important; }
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
        applyFilterMode();
    });
</script>
@endpush
