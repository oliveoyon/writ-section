@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 filing-print-page">
    <div class="filing-header mb-3">
        <div>
            <div class="system-mark">RTFTS Filing</div>
            <h4 class="mb-0">Barcode Print</h4>
            <small>Find a permanent case and print its label.</small>
        </div>
        <a href="{{ route('admin.tracking.filing.index') }}" class="btn btn-outline-brand btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.tracking.filing.print-index') }}" class="admin-panel mb-3">
        <div class="panel-heading">
            <div>
                <h5>Search File</h5>
                <span>Use barcode or WRPET reference</span>
            </div>
        </div>
        <div class="panel-body">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Barcode or WRPET Reference</label>
                <input type="text" name="permanent_barcode" class="form-control" value="{{ $barcode }}" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-brand w-100">{{ __('tracking.filing.search_print') }}</button>
            </div>
        </div>
        </div>
    </form>

    @if($barcode !== '' && !$case)
        <div class="alert alert-warning">{{ __('tracking.filing.permanent_not_found') }}</div>
    @endif

    @if($case)
        <div class="admin-panel result-panel">
            <div class="panel-heading">
                <div>
                    <h5>{{ $case->case_reference }}</h5>
                    <span>{{ $case->permanent_barcode }}</span>
                </div>
            </div>
            <div class="panel-body">
            <a href="{{ route('admin.tracking.filing.print-label', ['case' => $case->id, 'width_mm' => 50, 'height_mm' => 25, 'auto' => 1]) }}"
               target="_blank"
               class="btn btn-gold">
                <i class="bi bi-printer"></i>
                Print Barcode
            </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('css')
<style>
    .filing-print-page { max-width: 860px; }
    .filing-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .85rem 1rem; background: #fff; border: 1px solid #e3e8ef; border-top: 3px solid #00284d; border-bottom-color: #d4a017; border-radius: 4px; box-shadow: 0 1px 5px rgba(0, 40, 77, .08); }
    .filing-header h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
    .filing-header small { color: #6b7280; font-weight: 600; }
    .system-mark { color: #b87d08; font-size: .82rem; font-weight: 800; }
    .admin-panel { background: #fff; border: 1px solid #e3e8ef; border-radius: 4px; box-shadow: 0 1px 5px rgba(0, 40, 77, .07); overflow: hidden; }
    .panel-heading { padding: .75rem 1rem; background: #fbfcfe; border-top: 3px solid #00284d; border-bottom: 1px solid #e5e7eb; }
    .panel-heading h5 { margin: 0; color: #1f2937; font-size: 1rem; font-weight: 800; }
    .panel-heading span { color: #6b7280; font-size: .84rem; font-weight: 600; }
    .panel-body { padding: 1rem; }
    .form-label { color: #374151; font-size: .84rem; font-weight: 800; }
    .form-control { border-radius: 4px; }
    .form-control:focus { border-color: #d4a017; box-shadow: 0 0 0 .15rem rgba(212, 160, 23, .15); }
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-gold { background: #d4a017; color: #111827; border-color: #d4a017; border-radius: 4px; font-weight: 800; }
    .btn-gold:hover { background: #b38b0f; color: #fff; border-color: #b38b0f; }
    .btn-outline-brand { color: #00284d; border-color: #00284d; border-radius: 4px; font-weight: 800; }
    .btn-outline-brand:hover { background: #00284d; color: #fff; border-color: #00284d; }
    @media (max-width: 575.98px) {
        .filing-header { align-items: stretch; flex-direction: column; }
        .filing-header .btn { width: 100%; }
    }
</style>
@endpush
