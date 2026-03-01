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

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <form method="POST" action="{{ route('admin.tracking.section.receive.store') }}" class="card p-4 h-100">
                @csrf
                <input type="hidden" name="action" value="receive">

                <h5 class="mb-2">{{ __('tracking.receive.bulk_title') }}</h5>
                <p class="text-muted mb-3">{{ __('tracking.receive.bulk_subtitle') }}</p>

                <label for="barcodes" class="form-label">{{ __('tracking.receive.bulk_input_label') }}</label>
                <textarea id="barcodes" name="barcodes" class="form-control form-control-lg" rows="7" placeholder="{{ __('tracking.receive.bulk_placeholder') }}" autofocus>{{ old('barcodes') }}</textarea>

                <div class="mt-3">
                    <label for="reason_receive" class="form-label">{{ __('tracking.receive.reason_optional') }}</label>
                    <textarea id="reason_receive" name="reason" class="form-control" rows="2">{{ old('reason') }}</textarea>
                </div>

                <button type="submit" class="btn btn-brand mt-3">{{ __('tracking.receive.submit_bulk') }}</button>
            </form>
        </div>

        <div class="col-12 col-lg-4">
            @if ($isAffidavit)
                <form method="POST" action="{{ route('admin.tracking.section.receive.store') }}" class="card p-4 mb-3">
                    @csrf
                    <input type="hidden" name="action" value="reject">
                    <h5 class="mb-2">{{ __('tracking.receive.reject_title') }}</h5>
                    <p class="text-muted mb-3">{{ __('tracking.receive.reject_subtitle') }}</p>

                    <label for="barcode" class="form-label">{{ __('tracking.receive.barcode') }}</label>
                    <input type="text" id="barcode" name="barcode" class="form-control" placeholder="{{ __('tracking.receive.barcode_placeholder') }}" required>

                    <div class="mt-3">
                        <label for="reason" class="form-label">{{ __('tracking.receive.reason_optional') }}</label>
                        <textarea id="reason" name="reason" class="form-control" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-danger mt-3">{{ __('tracking.receive.submit_reject') }}</button>
                </form>
            @endif

            <div class="card p-4">
                <h6 class="mb-2">{{ __('tracking.receive.tip_title') }}</h6>
                <ul class="mb-0 small text-muted">
                    <li>{{ __('tracking.receive.tip_1') }}</li>
                    <li>{{ __('tracking.receive.tip_2') }}</li>
                    <li>{{ __('tracking.receive.tip_3') }}</li>
                </ul>
            </div>
        </div>
    </div>

    @php($summary = session('receive_summary'))
    @if($summary && is_array($summary))
        <div class="card p-4 mt-3" id="receivePrintArea">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">{{ __('tracking.receive.receipt_title') }}</h5>
                    <div class="small text-muted">{{ __('tracking.receive.receipt_subtitle') }}</div>
                </div>
                <button type="button" class="btn btn-gold no-print" onclick="window.print()">
                    {{ __('tracking.receive.print_copy') }}
                </button>
            </div>

            <div class="row mb-3">
                <div class="col-md-4"><strong>{{ __('tracking.receive.receipt_section') }}:</strong> {{ $summary['section'] ?? '-' }}</div>
                <div class="col-md-4"><strong>{{ __('tracking.receive.receipt_user') }}:</strong> {{ $summary['received_by'] ?? '-' }}</div>
                <div class="col-md-4"><strong>{{ __('tracking.receive.receipt_time') }}:</strong> {{ $summary['received_at'] ?? '-' }}</div>
            </div>

            <h6 class="mb-2">{{ __('tracking.receive.received_files') }}</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('tracking.receive.barcode') }}</th>
                            <th>{{ __('tracking.receive.receipt_case_no') }}</th>
                            <th>{{ __('tracking.receive.receipt_from') }}</th>
                            <th>{{ __('tracking.receive.receipt_to') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($summary['received'] ?? []) as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item['barcode'] ?? '-' }}</td>
                                <td>{{ $item['case_no'] ?? '-' }}</td>
                                <td>{{ $item['from_section'] ?? '-' }}</td>
                                <td>{{ $item['to_section'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">{{ __('tracking.receive.none') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(!empty($summary['failed']))
                <h6 class="mt-3 mb-2">{{ __('tracking.receive.failed_files') }}</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('tracking.receive.barcode') }}</th>
                                <th>{{ __('tracking.receive.fail_reason') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summary['failed'] as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item['barcode'] ?? '-' }}</td>
                                    <td>{{ $item['reason'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="row mt-4 pt-4 border-top">
                <div class="col-md-6 text-center">
                    <div class="signature-line"></div>
                    <div>{{ __('tracking.receive.receiver_signature') }}</div>
                </div>
                <div class="col-md-6 text-center mt-4 mt-md-0">
                    <div class="signature-line"></div>
                    <div>{{ __('tracking.receive.supervisor_signature') }}</div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('css')
<style>
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-gold { background: #d4a017; color: #1f2933; border-color: #d4a017; }
    .btn-gold:hover { background: #bc8d12; color: #fff; border-color: #bc8d12; }
    .signature-line { border-bottom: 1px solid #222; height: 28px; margin-bottom: 8px; }
    @media print {
        .navbar, footer, .no-print { display: none !important; }
        main { padding-top: 0 !important; }
        #receivePrintArea { border: 0 !important; box-shadow: none !important; }
    }
</style>
@endpush
