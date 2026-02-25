@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4">
    <h3 class="mb-3">{{ __('tracking.lookup.title') }}</h3>

    <form method="GET" action="{{ route('admin.tracking.lookup') }}" class="card p-3 mb-3">
        <label for="q" class="form-label">{{ __('tracking.lookup.search_label') }}</label>
        <div class="d-flex gap-2">
            <input type="text" id="q" name="q" class="form-control" value="{{ request('q') }}" required>
            <button type="submit" class="btn btn-brand">{{ __('tracking.lookup.search') }}</button>
        </div>
    </form>

    @if ($case)
        <div class="card p-3">
            <h5 class="mb-2">{{ __('tracking.lookup.current_location') }}</h5>
            <p class="mb-1"><strong>{{ __('tracking.lookup.case') }}:</strong> {{ $case->final_case_number ?? __('tracking.lookup.na') }}</p>
            <p class="mb-1"><strong>{{ __('tracking.lookup.permanent_barcode') }}:</strong> {{ $case->permanent_barcode ?? __('tracking.lookup.na') }}</p>
            <p class="mb-1"><strong>{{ __('tracking.lookup.current_section') }}:</strong> {{ $case->current_section ?? __('tracking.lookup.na') }}</p>
            <p class="mb-3"><strong>{{ __('tracking.lookup.responsible_person') }}:</strong> {{ $case->currentHolder?->name ?? __('tracking.lookup.na') }}</p>

            <a href="{{ route('admin.tracking.timeline', $case) }}" class="btn btn-outline-brand">{{ __('tracking.lookup.timeline') }}</a>
        </div>
    @elseif(request()->filled('q'))
        <div class="alert alert-warning">{{ __('tracking.lookup.not_found') }}</div>
    @endif
</div>
@endsection

@push('css')
<style>
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-outline-brand { color: #00284d; border-color: #00284d; }
    .btn-outline-brand:hover { color: #fff; background: #00284d; border-color: #00284d; }
</style>
@endpush
