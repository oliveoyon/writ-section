@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4">
    <h3 class="mb-3">Barcode Print</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.tracking.filing.print-index') }}" class="card p-3 mb-3 print-card">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Barcode or WRPET Reference</label>
                <input type="text" name="permanent_barcode" class="form-control" value="{{ $barcode }}" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-brand w-100">{{ __('tracking.filing.search_print') }}</button>
            </div>
        </div>
    </form>

    @if($barcode !== '' && !$case)
        <div class="alert alert-warning">{{ __('tracking.filing.permanent_not_found') }}</div>
    @endif

    @if($case)
        <div class="card p-3 print-card">
            <p class="mb-1"><strong>Reference:</strong> {{ $case->case_reference }}</p>
            <p class="mb-3"><strong>{{ __('tracking.filing.permanent_barcode') }}:</strong> {{ $case->permanent_barcode }}</p>
            <a href="{{ route('admin.tracking.filing.print-label', ['case' => $case->id, 'width_mm' => 50, 'height_mm' => 25, 'auto' => 1]) }}"
               target="_blank"
               class="btn btn-gold btn-lg">
                Print Barcode
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
    .print-card { max-width: 760px; }
</style>
@endpush
