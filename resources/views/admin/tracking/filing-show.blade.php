@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">{{ __('tracking.filing.details_title') }}</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tracking.filing.print-label', ['case' => $case->id, 'auto' => 1]) }}" class="btn btn-gold">
                {{ __('tracking.filing.print_now') }}
            </a>
            <a href="{{ route('admin.tracking.filing.index') }}" class="btn btn-outline-brand">{{ __('tracking.filing.back_to_module') }}</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-4"><strong>{{ __('tracking.filing.case_no') }}:</strong> {{ $case->final_case_number ?? '-' }}</div>
            <div class="col-md-4"><strong>{{ __('tracking.lookup.permanent_barcode') }}:</strong> {{ $case->permanent_barcode ?? '-' }}</div>
            <div class="col-md-4"><strong>{{ __('tracking.lookup.current_section') }}:</strong> {{ $case->current_section ?? '-' }}</div>
            <div class="col-md-6"><strong>{{ __('tracking.filing.subject') }}:</strong> {{ $case->subject ?? '-' }}</div>
            <div class="col-md-6"><strong>{{ __('tracking.filing.lawyer_name') }}:</strong> {{ $case->lawyer?->full_name ?? '-' }}</div>
        </div>
    </div>

    <div class="card p-3 mb-3">
        <h5>{{ __('tracking.filing.petitioners') }}</h5>
        <ul class="mb-0">
            @forelse($case->petitioners as $p)
                <li>{{ $p->name_or_organization }} @if($p->phone) ({{ $p->phone }}) @endif</li>
            @empty
                <li class="text-muted">{{ __('tracking.filing.no_data') }}</li>
            @endforelse
        </ul>
    </div>

    <div class="card p-3 mb-3">
        <h5>{{ __('tracking.filing.respondents') }}</h5>
        <ul class="mb-0">
            @forelse($case->respondents as $r)
                <li>{{ $r->name }} @if($r->designation) - {{ $r->designation }} @endif</li>
            @empty
                <li class="text-muted">{{ __('tracking.filing.no_data') }}</li>
            @endforelse
        </ul>
    </div>

    <div class="card p-3">
        <h5>{{ __('tracking.timeline.history') }}</h5>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('tracking.timeline.time') }}</th>
                        <th>{{ __('tracking.timeline.from') }}</th>
                        <th>{{ __('tracking.timeline.to') }}</th>
                        <th>{{ __('tracking.timeline.type') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($case->movements as $move)
                        <tr>
                            <td>{{ optional($move->received_at)->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $move->from_section ?? '-' }}</td>
                            <td>{{ $move->to_section }}</td>
                            <td>{{ $move->movement_type }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted text-center">{{ __('tracking.filing.no_data') }}</td>
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
    .btn-outline-brand { color: #00284d; border-color: #00284d; }
    .btn-outline-brand:hover { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-gold { background: #d4a017; color: #fff; border-color: #d4a017; }
    .btn-gold:hover { background: #b38b0f; color: #fff; border-color: #b38b0f; }
</style>
@endpush
