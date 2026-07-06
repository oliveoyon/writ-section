@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4">
    <h3 class="mb-3">{{ __('tracking.timeline.title') }}: {{ $case->case_reference ?? __('tracking.timeline.case_prefix').' #'.$case->id }}</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card p-3 mb-3">
        <h5>{{ __('tracking.timeline.current_status') }}</h5>
        <p class="mb-1"><strong>{{ __('tracking.timeline.section') }}:</strong> {{ $case->current_section ?? __('tracking.timeline.na') }}</p>
        <p class="mb-1"><strong>{{ __('tracking.timeline.responsible') }}:</strong> {{ $case->currentHolder?->name ?? __('tracking.timeline.na') }}</p>
    </div>

    <div class="card p-3 mb-3">
        <h5 class="mb-3">{{ __('tracking.timeline.registrar_override') }}</h5>
        @if($case->permanent_barcode)
            <form method="POST" action="{{ route('admin.tracking.override', $case) }}">
                @csrf
                <div class="mb-3">
                    <label for="to_department_id" class="form-label">{{ __('tracking.timeline.move_to_section') }}</label>
                    <select id="to_department_id" name="to_department_id" class="form-select" required>
                        <option value="">{{ __('tracking.timeline.select_section') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) old('to_department_id') === (string) $department->id)>
                                {{ $department->label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="reason" class="form-label">{{ __('tracking.timeline.override_reason') }}</label>
                    <select id="reason" name="reason" class="form-select" required>
                        <option value="">{{ __('tracking.timeline.select_reason') }}</option>
                        @foreach($overrideReasons as $value => $label)
                            <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-gold">{{ __('tracking.timeline.apply_override') }}</button>
            </form>
        @else
            <div class="alert alert-warning mb-0">{{ __('tracking.timeline.permanent_barcode_required') }}</div>
        @endif
    </div>

    <div class="card p-3">
        <h5 class="mb-3">{{ __('tracking.timeline.history') }}</h5>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>{{ __('tracking.timeline.time') }}</th>
                        <th>{{ __('tracking.timeline.from') }}</th>
                        <th>{{ __('tracking.timeline.to') }}</th>
                        <th>{{ __('tracking.timeline.type') }}</th>
                        <th>{{ __('tracking.timeline.by') }}</th>
                        <th>{{ __('tracking.timeline.reason_notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $move)
                        <tr>
                            <td>{{ optional($move->received_at)->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $move->from_section ?? '-' }}</td>
                            <td>{{ $move->to_section }}</td>
                            <td>{{ $move->movement_type }}</td>
                            <td>{{ $move->receivedBy?->name ?? __('tracking.timeline.na') }}</td>
                            <td>{{ $move->override_reason ?? $move->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">{{ __('tracking.timeline.no_movement') }}</td>
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
    .btn-gold { background: #d4a017; color: #fff; border-color: #d4a017; }
    .btn-gold:hover { background: #b38b0f; color: #fff; border-color: #b38b0f; }
</style>
@endpush
