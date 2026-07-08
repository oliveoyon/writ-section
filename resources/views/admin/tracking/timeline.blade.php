@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 timeline-page">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.tracking.lookup') }}" class="btn btn-sm btn-light border icon-button" title="Back to lookup"><i class="bi bi-arrow-left"></i></a>
            <h3 class="mb-0">{{ $case->case_reference ?? __('tracking.timeline.case_prefix').' #'.$case->id }}</h3>
        </div>
        @if($case->permanent_barcode)
            <button class="btn btn-gold" type="button" data-bs-toggle="collapse" data-bs-target="#overridePanel" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">
                <i class="bi bi-arrow-left-right me-1"></i>{{ __('tracking.timeline.registrar_override') }}
            </button>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="custody-strip mb-4">
        <div class="custody-icon"><i class="bi bi-geo-alt-fill"></i></div>
        <div>
            <span>{{ __('tracking.timeline.current_status') }}</span>
            <strong>{{ $case->current_section ?? __('tracking.timeline.na') }}</strong>
            <small>{{ $case->currentHolder?->name ?? __('tracking.timeline.na') }}</small>
        </div>
    </div>

    <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="overridePanel">
      <div class="override-panel">
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
    </div>

    @php
        $movementLabels = [
            'receive' => __('tracking.receive.receive'),
            'reject' => __('tracking.receive.reject'),
            'override_receive' => __('tracking.register.override_receive'),
            'dispatch_to_court' => __('tracking.register.dispatch_to_court'),
            'returned_from_court_handover' => __('tracking.register.returned_from_court_handover'),
            'returned_to_lawyer' => 'Returned to Lawyer',
        ];
        $previousHolderByMovement = [];
        $lastKnownHolder = null;
        foreach ($movements->sortBy('received_at') as $movement) {
            $previousHolderByMovement[$movement->id] = $lastKnownHolder;
            if (strtolower((string) $movement->to_section) !== 'court') {
                $lastKnownHolder = $movement->receivedBy?->name;
            }
        }
    @endphp
    <section class="history-panel">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h5 class="mb-0">{{ __('tracking.timeline.history') }}</h5>
            @if($movements->isNotEmpty())
                <div class="movement-filters" role="group" aria-label="Filter movements">
                    <button type="button" class="filter-button active" data-filter="all">All <span>{{ $movements->count() }}</span></button>
                    @foreach($movements->pluck('movement_type')->filter()->unique() as $type)
                        <button type="button" class="filter-button" data-filter="{{ $type }}">{{ $movementLabels[$type] ?? str($type)->replace('_', ' ')->title() }}</button>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table movement-table align-middle">
                <thead>
                    <tr>
                        <th>{{ __('tracking.timeline.time') }}</th>
                        <th>{{ __('tracking.timeline.from') }}</th>
                        <th>{{ __('tracking.timeline.to') }}</th>
                        <th>{{ __('tracking.timeline.type') }}</th>
                        <th>{{ __('tracking.timeline.reason_notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements->sortByDesc('received_at') as $move)
                        <tr class="movement-row" data-movement-type="{{ $move->movement_type }}">
                            <td><span class="movement-time">{{ optional($move->received_at)->format('d M Y') }}</span><small>{{ optional($move->received_at)->format('h:i A') }}</small></td>
                            <td>
                                <span class="section-name">{{ $move->from_section ?? '-' }}</span>
                                @if(strtolower((string) $move->from_section) !== 'court' && ($previousHolderByMovement[$move->id] ?? null))
                                    <small>{{ $previousHolderByMovement[$move->id] }}</small>
                                @endif
                            </td>
                            <td>
                                <strong class="section-name">{{ $move->to_section }}</strong>
                                @if(strtolower((string) $move->to_section) !== 'court' && $move->receivedBy?->name)
                                    <small>{{ $move->receivedBy->name }}</small>
                                @endif
                            </td>
                            <td><span class="movement-badge type-{{ $move->movement_type }}">{{ $movementLabels[$move->movement_type] ?? str($move->movement_type)->replace('_', ' ')->title() }}</span></td>
                            <td>{{ $move->override_reason ?? $move->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">{{ __('tracking.timeline.no_movement') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="empty-filter d-none" id="emptyFilter"><i class="bi bi-funnel"></i>{{ __('tracking.timeline.no_movement') }}</div>
    </section>
</div>
@endsection

@push('css')
<style>
    .timeline-page { max-width: 1080px; }
    .timeline-page h3 { color: #1f2937; font-size: 1.45rem; font-weight: 700; }
    .icon-button { width: 36px; height: 36px; display: inline-grid; place-items: center; padding: 0; }
    .btn-gold { background: #d4a017; color: #fff; border-color: #d4a017; }
    .btn-gold:hover { background: #b38b0f; color: #fff; border-color: #b38b0f; }
    .custody-strip { display: flex; align-items: center; gap: 18px; padding: 18px 20px; border: 1px solid #cbd5e1; border-left: 5px solid #0f766e; border-radius: 6px; background: #fff; }
    .custody-icon { width: 42px; height: 42px; display: grid; place-items: center; flex: 0 0 auto; border-radius: 50%; color: #fff; background: #0f766e; }
    .custody-strip span { display: block; color: #64748b; font-size: .74rem; font-weight: 700; text-transform: uppercase; }
    .custody-strip strong { color: #1f2937; }
    .custody-strip small { display: block; margin-top: 2px; color: #64748b; }
    .custody-divider { align-self: stretch; width: 1px; background: #e2e8f0; }
    .override-panel { padding: 18px; border: 1px solid #e2e8f0; border-radius: 6px; background: #f8fafc; }
    .history-panel { padding-top: 4px; }
    .movement-filters { display: flex; flex-wrap: wrap; gap: 6px; }
    .filter-button { padding: 5px 10px; border: 1px solid #cbd5e1; border-radius: 5px; background: #fff; color: #475569; font-size: .8rem; font-weight: 650; }
    .filter-button:hover, .filter-button.active { border-color: #0f766e; background: #0f766e; color: #fff; }
    .movement-table { border: 1px solid #e2e8f0; }
    .movement-table thead th { padding: 10px 12px; border-bottom-width: 1px; background: #f8fafc; color: #64748b; font-size: .75rem; text-transform: uppercase; }
    .movement-table td { padding: 12px; border-color: #edf2f7; }
    .movement-row { transition: background-color .15s; }
    .movement-row:hover { background: #f8fafc; }
    .movement-time { display: block; white-space: nowrap; font-weight: 650; }
    .movement-table small { display: block; color: #64748b; }
    .section-name { display: block; }
    .movement-badge { display: inline-block; padding: 4px 7px; border-radius: 4px; background: #e6f5f2; color: #0f766e; font-size: .72rem; font-weight: 750; }
    .movement-badge.type-reject { background: #fee2e2; color: #b91c1c; }
    .movement-badge.type-override_receive { background: #fef3c7; color: #92400e; }
    .movement-badge.type-dispatch_to_court, .movement-badge.type-returned_from_court_handover { background: #dbeafe; color: #1d4ed8; }
    .empty-filter { padding: 30px; align-items: center; justify-content: center; gap: 8px; border: 1px dashed #cbd5e1; border-radius: 6px; color: #64748b; }
    .empty-filter:not(.d-none) { display: flex; }
    @media (max-width: 575.98px) {
        .timeline-page h3 { font-size: 1.15rem; }
        .custody-strip { align-items: flex-start; flex-wrap: wrap; gap: 12px; }
        .custody-divider { display: none; }
        .movement-filters { width: 100%; flex-wrap: nowrap; overflow-x: auto; padding-bottom: 4px; }
        .filter-button { white-space: nowrap; }
    }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const rows = [...document.querySelectorAll('.movement-row')];
    const emptyFilter = document.getElementById('emptyFilter');

    document.querySelectorAll('.filter-button').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.filter-button').forEach((item) => item.classList.remove('active'));
            button.classList.add('active');
            const filter = button.dataset.filter;
            let visible = 0;

            rows.forEach((row) => {
                const show = filter === 'all' || row.dataset.movementType === filter;
                row.classList.toggle('d-none', !show);
                if (show) visible += 1;
            });

            emptyFilter?.classList.toggle('d-none', visible > 0);
        });
    });
});
</script>
@endpush
