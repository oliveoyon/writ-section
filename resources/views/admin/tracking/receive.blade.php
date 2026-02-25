@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4">
    <h3 class="mb-2">{{ $section }}: {{ __('tracking.receive.title') }}</h3>
    <p class="text-muted mb-3">{{ __('tracking.receive.subtitle') }}</p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.tracking.section.receive.store') }}" class="card p-4">
        @csrf

        <label for="barcode" class="form-label">{{ __('tracking.receive.barcode') }}</label>
        <input type="text" id="barcode" name="barcode" class="form-control form-control-lg" placeholder="{{ __('tracking.receive.barcode_placeholder') }}" autofocus required>

        @if ($isAffidavit)
            <div class="mt-3">
                <label for="action" class="form-label">{{ __('tracking.receive.action') }}</label>
                <select name="action" id="action" class="form-select" required>
                    <option value="receive">{{ __('tracking.receive.receive') }}</option>
                    <option value="reject">{{ __('tracking.receive.reject') }}</option>
                </select>
            </div>
            <div class="mt-3">
                <label for="reason" class="form-label">{{ __('tracking.receive.reason_optional') }}</label>
                <textarea id="reason" name="reason" class="form-control" rows="3"></textarea>
            </div>
        @else
            <input type="hidden" name="action" value="receive">
        @endif

        <button type="submit" class="btn btn-brand mt-3">{{ __('tracking.receive.submit') }}</button>
    </form>
</div>
@endsection

@push('css')
<style>
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
</style>
@endpush
