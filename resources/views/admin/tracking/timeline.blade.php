@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 timeline-page">
    <div class="timeline-header mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.tracking.lookup') }}" class="btn btn-action" title="Back to lookup"><i class="bi bi-arrow-left"></i></a>
            <div>
                <div class="system-mark">RTFTS Timeline</div>
                <h4 class="mb-0">{{ $case->case_reference ?? __('tracking.timeline.case_prefix').' #'.$case->id }}</h4>
                <small>{{ $case->permanent_barcode ?? '-' }}</small>
            </div>
        </div>
        @if($case->permanent_barcode)
            <button class="btn btn-gold btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#overridePanel" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">
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

    <div class="custody-strip mb-3">
        <div class="custody-icon"><i class="bi bi-geo-alt-fill"></i></div>
        <div>
            <span>{{ __('tracking.timeline.current_status') }}</span>
            <strong>{{ $case->current_section ?? __('tracking.timeline.na') }}</strong>
            <small>{{ $case->currentHolder?->name ?? __('tracking.timeline.na') }}</small>
        </div>
    </div>

    <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-3" id="overridePanel">
      <div class="override-panel admin-panel">
        <div class="panel-heading warning">
            <div>
                <h5>{{ __('tracking.timeline.registrar_override') }}</h5>
                <span>{{ $case->case_reference ?? '-' }}</span>
            </div>
        </div>
        <div class="panel-body">
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
    </div>

    @php
        $movementLabels = [
            'receive' => __('tracking.receive.receive'),
            'reject' => __('tracking.receive.reject'),
            'override_receive' => __('tracking.register.override_receive'),
            'dispatch_to_court' => __('tracking.register.dispatch_to_court'),
            'returned_from_court_handover' => __('tracking.register.returned_from_court_handover'),
            'returned_to_lawyer' => 'Returned to Lawyer',
            'legacy_intake' => __('tracking.register.old_case_receive'),
            'legacy_receive' => __('tracking.register.old_case_receive'),
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
    <section class="history-panel admin-panel">
        <div class="panel-heading">
            <div>
                <h5 class="mb-0">Writ Journey</h5>
                <span>{{ $movements->count() }} movement(s)</span>
            </div>
            @if($movements->isNotEmpty())
                <div class="movement-filters" role="group" aria-label="Filter movements">
                    <button type="button" class="filter-button active" data-filter="all">All <span>{{ $movements->count() }}</span></button>
                    @foreach($movements->pluck('movement_type')->filter()->unique() as $type)
                        <button type="button" class="filter-button" data-filter="{{ $type }}">{{ $movementLabels[$type] ?? str($type)->replace('_', ' ')->title() }}</button>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="journey-map">
            @forelse ($movements->sortByDesc('received_at')->values() as $index => $move)
                @php
                    $movementText = $movementLabels[$move->movement_type] ?? str($move->movement_type)->replace('_', ' ')->title();
                    $fromHolder = strtolower((string) $move->from_section) !== 'court' ? ($previousHolderByMovement[$move->id] ?? null) : null;
                    $toHolder = strtolower((string) $move->to_section) !== 'court' ? $move->receivedBy?->name : null;
                    $journeyNote = $move->override_reason ?? $move->notes;
                @endphp
                <article class="journey-stop movement-row type-{{ $move->movement_type }}" data-movement-type="{{ $move->movement_type }}">
                    <div class="journey-rail">
                        <span class="journey-dot">{{ $index + 1 }}</span>
                        <span class="journey-line"></span>
                    </div>
                    <div class="journey-card">
                        <div class="journey-card-head">
                            <div>
                                <span class="movement-badge type-{{ $move->movement_type }}">{{ $movementText }}</span>
                                <h6>{{ optional($move->received_at)->format('d M Y') }} <small>{{ optional($move->received_at)->format('h:i A') }}</small></h6>
                            </div>
                            <span class="journey-step">{{ $index === 0 ? 'Latest' : 'Step ' . ($index + 1) }}</span>
                        </div>

                        <div class="journey-route">
                            <div class="route-point">
                                <span>{{ __('tracking.timeline.from') }}</span>
                                <strong>{{ $move->from_section ?? '-' }}</strong>
                                @if($fromHolder)
                                    <small>{{ $fromHolder }}</small>
                                @endif
                            </div>
                            <div class="route-arrow"><i class="bi bi-arrow-right"></i></div>
                            <div class="route-point destination">
                                <span>{{ __('tracking.timeline.to') }}</span>
                                <strong>{{ $move->to_section ?? '-' }}</strong>
                                @if($toHolder)
                                    <small>{{ $toHolder }}</small>
                                @endif
                            </div>
                        </div>

                        @if($journeyNote)
                            <div class="journey-note">
                                <i class="bi bi-chat-left-text"></i>
                                <span>{{ $journeyNote }}</span>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="journey-empty">{{ __('tracking.timeline.no_movement') }}</div>
            @endforelse
        </div>
        <div class="empty-filter d-none" id="emptyFilter"><i class="bi bi-funnel"></i>{{ __('tracking.timeline.no_movement') }}</div>
    </section>
</div>
@endsection

@push('css')
<style>
    .timeline-page { max-width: 1080px; }
    .timeline-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        background: #fff;
        border: 1px solid #e3e8ef;
        border-top: 3px solid #00284d;
        border-bottom-color: #d4a017;
        border-radius: 4px;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .08);
    }
    .timeline-header h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
    .timeline-header small { color: #6b7280; font-weight: 600; }
    .system-mark { color: #b87d08; font-size: .82rem; font-weight: 800; }
    .admin-panel { background: #fff; border: 1px solid #e3e8ef; border-radius: 4px; box-shadow: 0 1px 5px rgba(0, 40, 77, .07); overflow: hidden; }
    .panel-heading { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .75rem 1rem; background: #fbfcfe; border-top: 3px solid #00284d; border-bottom: 1px solid #e5e7eb; }
    .panel-heading.warning { border-top-color: #d4a017; }
    .panel-heading h5 { margin: 0; color: #1f2937; font-size: 1rem; font-weight: 800; }
    .panel-heading span { color: #6b7280; font-size: .84rem; font-weight: 600; }
    .panel-body { padding: 1rem; }
    .btn-action { width: 2rem; height: 2rem; display: inline-grid; place-items: center; padding: 0; border-radius: 4px; border: 1px solid #bcd0e2; color: #0b4f8a; background: #f2f7fc; }
    .btn-action:hover { background: #0b4f8a; color: #fff; }
    .btn-gold { background: #d4a017; color: #1f2933; border-color: #d4a017; font-weight: 800; }
    .btn-gold:hover { background: #b38b0f; color: #fff; border-color: #b38b0f; }
    .custody-strip { display: flex; align-items: center; gap: 18px; padding: 1rem; border: 1px solid #e3e8ef; border-left: 4px solid #0f766e; border-radius: 4px; background: #fff; box-shadow: 0 1px 5px rgba(0, 40, 77, .06); }
    .custody-icon { width: 42px; height: 42px; display: grid; place-items: center; flex: 0 0 auto; border-radius: 4px; color: #fff; background: #0f766e; }
    .custody-strip span { display: block; color: #64748b; font-size: .74rem; font-weight: 700; text-transform: uppercase; }
    .custody-strip strong { color: #1f2937; }
    .custody-strip small { display: block; margin-top: 2px; color: #64748b; }
    .custody-divider { align-self: stretch; width: 1px; background: #e2e8f0; }
    .override-panel .form-label { color: #374151; font-size: .84rem; font-weight: 800; }
    .override-panel .form-select { border-radius: 4px; min-height: 42px; }
    .override-panel .form-select:focus { border-color: #d4a017; box-shadow: 0 0 0 .15rem rgba(212, 160, 23, .15); }
    .movement-filters { display: flex; flex-wrap: wrap; gap: 6px; }
    .filter-button { padding: 5px 10px; border: 1px solid #cbd5e1; border-radius: 5px; background: #fff; color: #475569; font-size: .8rem; font-weight: 650; }
    .filter-button:hover, .filter-button.active { border-color: #0f766e; background: #0f766e; color: #fff; }
    .journey-map { padding: .75rem .85rem .9rem; }
    .journey-stop { display: grid; grid-template-columns: 36px 1fr; gap: .65rem; position: relative; }
    .journey-stop + .journey-stop { margin-top: .55rem; }
    .journey-rail { position: relative; display: flex; justify-content: center; }
    .journey-dot {
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        border-radius: 4px;
        background: #00284d;
        color: #fff;
        font-size: .76rem;
        font-weight: 800;
        position: relative;
        z-index: 2;
    }
    .journey-line { position: absolute; top: 28px; bottom: -.65rem; width: 2px; background: #d8e3ef; }
    .journey-stop:last-child .journey-line { display: none; }
    .journey-card {
        border: 1px solid #e3e8ef;
        border-left: 4px solid #0f766e;
        border-radius: 4px;
        background: #fff;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .06);
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .journey-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(0, 40, 77, .08); }
    .journey-stop.type-reject .journey-card { border-left-color: #b91c1c; }
    .journey-stop.type-override_receive .journey-card { border-left-color: #d4a017; }
    .journey-stop.type-dispatch_to_court .journey-card,
    .journey-stop.type-returned_from_court_handover .journey-card { border-left-color: #2563eb; }
    .journey-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: .55rem .7rem;
        border-bottom: 1px solid #eef2f7;
        background: #fbfcfe;
    }
    .journey-card-head h6 { margin: .25rem 0 0; color: #1f2937; font-size: .92rem; font-weight: 800; }
    .journey-card-head h6 small { color: #64748b; font-weight: 700; margin-left: .35rem; }
    .journey-step { color: #64748b; font-size: .78rem; font-weight: 800; white-space: nowrap; }
    .journey-route {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 42px minmax(0, 1fr);
        gap: .65rem;
        align-items: stretch;
        padding: .65rem .7rem;
    }
    .route-point {
        border: 1px solid #d8e3ef;
        border-radius: 4px;
        background: #f7fbff;
        padding: .55rem .65rem;
        min-height: 66px;
    }
    .route-point.destination { border-color: #bce8ca; background: #f0fdfa; }
    .route-point span { display: block; color: #64748b; font-size: .72rem; font-weight: 800; text-transform: uppercase; }
    .route-point strong { display: block; color: #0b4f8a; margin-top: .18rem; font-size: .92rem; }
    .route-point small { display: block; color: #4b5563; margin-top: .18rem; font-weight: 700; }
    .route-arrow { display: grid; place-items: center; color: #d4a017; font-size: 1.25rem; }
    .journey-note {
        display: flex;
        gap: .5rem;
        align-items: flex-start;
        margin: 0 .7rem .7rem;
        padding: .5rem .6rem;
        border-radius: 4px;
        background: #fff7e6;
        color: #805500;
        font-size: .84rem;
        font-weight: 700;
    }
    .journey-note i { margin-top: .1rem; }
    .journey-empty { padding: 2rem; color: #64748b; text-align: center; }
    .movement-badge { display: inline-block; padding: 4px 7px; border-radius: 4px; background: #e6f5f2; color: #0f766e; font-size: .72rem; font-weight: 750; }
    .movement-badge.type-reject { background: #fee2e2; color: #b91c1c; }
    .movement-badge.type-override_receive { background: #fef3c7; color: #92400e; }
    .movement-badge.type-dispatch_to_court, .movement-badge.type-returned_from_court_handover { background: #dbeafe; color: #1d4ed8; }
    .empty-filter { padding: 30px; align-items: center; justify-content: center; gap: 8px; border-top: 1px dashed #cbd5e1; color: #64748b; }
    .empty-filter:not(.d-none) { display: flex; }
    @media (max-width: 575.98px) {
        .timeline-header { align-items: stretch; flex-direction: column; }
        .timeline-header .btn-gold { width: 100%; }
        .custody-strip { align-items: flex-start; flex-wrap: wrap; gap: 12px; }
        .custody-divider { display: none; }
        .movement-filters { width: 100%; flex-wrap: nowrap; overflow-x: auto; padding-bottom: 4px; }
        .filter-button { white-space: nowrap; }
        .journey-stop { grid-template-columns: 32px 1fr; gap: .6rem; }
        .journey-dot { width: 28px; height: 28px; }
        .journey-line { top: 28px; }
        .journey-card-head { flex-direction: column; gap: .4rem; }
        .journey-route { grid-template-columns: 1fr; }
        .route-arrow { min-height: 28px; transform: rotate(90deg); }
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
