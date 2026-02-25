@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4">
    <h3 class="mb-3">{{ __('tracking.filing.print_module_title') }}</h3>
    <p class="text-muted">{{ __('tracking.filing.print_module_subtitle') }}</p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.tracking.filing.print-index') }}" class="card p-3 mb-3">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('tracking.filing.permanent_barcode') }}</label>
                <input type="text" name="permanent_barcode" class="form-control" value="{{ $barcode }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('tracking.filing.paper_width_mm') }}</label>
                <input type="number" min="30" max="110" step="1" name="width_mm" class="form-control" value="{{ $widthMm }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('tracking.filing.paper_height_mm') }}</label>
                <input type="number" min="20" max="150" step="1" name="height_mm" class="form-control" value="{{ $heightMm }}" required>
            </div>
        </div>
        <button type="submit" class="btn btn-brand mt-3">{{ __('tracking.filing.search_print') }}</button>
    </form>

    @if($barcode !== '' && !$case)
        <div class="alert alert-warning">{{ __('tracking.filing.permanent_not_found') }}</div>
    @endif

    @if($case)
        <div class="card p-3">
            <p class="mb-1"><strong>{{ __('tracking.filing.case_no') }}:</strong> {{ $case->final_case_number ?? '-' }}</p>
            <p class="mb-1"><strong>{{ __('tracking.filing.subject') }}:</strong> {{ $case->subject ?? '-' }}</p>
            <p class="mb-3"><strong>{{ __('tracking.filing.permanent_barcode') }}:</strong> {{ $case->permanent_barcode }}</p>
            <a class="btn btn-gold"
               href="{{ route('admin.tracking.filing.print-label', ['case' => $case->id, 'width_mm' => $widthMm, 'height_mm' => $heightMm, 'auto' => 1]) }}">
                {{ __('tracking.filing.print_now') }}
            </a>
            <a class="btn btn-outline-brand"
               href="{{ route('admin.tracking.filing.print-label-pdf', ['case' => $case->id, 'width_mm' => $widthMm, 'height_mm' => $heightMm]) }}"
               target="_blank">
                {{ __('tracking.filing.print_pdf') }}
            </a>
        </div>
    @endif
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
