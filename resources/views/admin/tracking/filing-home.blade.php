@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4">
    <h3 class="mb-2">{{ __('tracking.filing.module_title') }}</h3>
    <p class="text-muted">{{ __('tracking.filing.module_subtitle') }}</p>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card p-3 h-100">
                <h6>{{ __('tracking.filing.pending_temp') }}</h6>
                <h2 class="mb-0">{{ $pendingTempCount }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 h-100">
                <h6>{{ __('tracking.filing.current_section') }}</h6>
                <h5 class="mb-0">{{ $section }}</h5>
            </div>
        </div>
        <div class="col-md-4 d-flex align-items-stretch">
            <div class="card p-3 h-100 w-100">
                <h6>{{ __('tracking.filing.quick_actions') }}</h6>
                <div class="d-grid gap-2">
                    <a class="btn btn-brand" href="{{ route('admin.tracking.filing.scan-temp') }}">{{ __('tracking.filing.scan_title') }}</a>
                    <a class="btn btn-gold" href="{{ route('admin.tracking.filing.direct-create') }}">{{ __('tracking.filing.direct_title') }}</a>
                    <a class="btn btn-outline-brand" href="{{ route('admin.tracking.filing.print-index') }}">{{ __('tracking.filing.print_module_title') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3">
        <h5 class="mb-3">{{ __('tracking.filing.recent_files') }}</h5>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>{{ __('tracking.filing.case_no') }}</th>
                        <th>{{ __('tracking.filing.subject') }}</th>
                        <th>{{ __('tracking.filing.lawyer_name') }}</th>
                        <th>{{ __('tracking.filing.current_holder') }}</th>
                        <th>{{ __('tracking.filing.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentCases as $item)
                        <tr>
                            <td>{{ $item->final_case_number ?? '-' }}</td>
                            <td>{{ $item->subject ?? '-' }}</td>
                            <td>{{ $item->lawyer?->full_name ?? '-' }}</td>
                            <td>{{ $item->currentHolder?->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('admin.tracking.filing.show', $item) }}" class="btn btn-sm btn-outline-brand">{{ __('tracking.filing.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">{{ __('tracking.filing.no_data') }}</td>
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
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-gold { background: #d4a017; color: #fff; border-color: #d4a017; }
    .btn-gold:hover { background: #b38b0f; color: #fff; border-color: #b38b0f; }
    .btn-outline-brand { color: #00284d; border-color: #00284d; }
    .btn-outline-brand:hover { background: #00284d; color: #fff; border-color: #00284d; }
</style>
@endpush
