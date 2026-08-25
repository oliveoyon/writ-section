@extends('admin.layouts.adminlayout')

@section('content')
@php
    $movementLabels = [
        'receive' => 'Received',
        'reject' => 'Rejected',
        'dispatch_to_court' => 'Sent to Court',
        'returned_from_court_handover' => 'Court Return',
        'override_receive' => 'Override',
        'legacy_intake' => 'Old Case Receive',
        'legacy_receive' => 'Old Case Receive',
    ];
    $activeTotal = max($pendingCount + $inProgressCount + $completedCount, 1);
    $pendingPercent = round(($pendingCount / $activeTotal) * 100);
    $progressPercent = round(($inProgressCount / $activeTotal) * 100);
    $completedPercent = round(($completedCount / $activeTotal) * 100);
    $dashboardUser = auth()->user();
    $dashboardDepartment = strtolower((string) ($dashboardUser?->departmentRelation?->name ?? ''));
    $canUseLookup = ($dashboardUser?->hasRole('Super Admin') ?? false) || str_contains($dashboardDepartment, 'registrar');
@endphp

<div class="container py-4 dashboard-page">
    <section class="dashboard-hero mb-3">
        <div>
            <div class="system-mark">RTFTS</div>
            <h4 class="mb-1">Real Time File Tracking Dashboard</h4>
            <p class="mb-0">Writ file movement, court dispatch and section custody.</p>
        </div>
        <div class="period-switch" aria-label="Report period">
            <a class="{{ $periodDays === 7 ? 'active' : '' }}" href="{{ route('admin.dashboard', ['period' => 7]) }}">7 Days</a>
            <a class="{{ $periodDays === 30 ? 'active' : '' }}" href="{{ route('admin.dashboard', ['period' => 30]) }}">30 Days</a>
            <a class="{{ $periodDays === 90 ? 'active' : '' }}" href="{{ route('admin.dashboard', ['period' => 90]) }}">90 Days</a>
        </div>
    </section>

    <section class="row g-3 mb-3">
        <div class="col-lg-3 col-md-6">
            <div class="metric-card primary">
                <div class="metric-icon"><i class="bi bi-folder2-open"></i></div>
                <span>Total Files</span>
                <strong>{{ $totalCases }}</strong>
                <small>All registered writ matters</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="metric-card warning">
                <div class="metric-icon"><i class="bi bi-hourglass-split"></i></div>
                <span>Pending Filing</span>
                <strong>{{ $pendingCount }}</strong>
                <small>{{ $pendingTempCount }} temporary barcode pending</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="metric-card info">
                <div class="metric-icon"><i class="bi bi-arrow-left-right"></i></div>
                <span>Moving Now</span>
                <strong>{{ $inProgressCount }}</strong>
                <small>{{ $inCourtCount }} currently in court</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="metric-card danger">
                <div class="metric-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <span>Need Attention</span>
                <strong>{{ $overdueCount }}</strong>
                <small>Active files older than 15 days</small>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="panel-card h-100">
                <div class="section-title">
                    <div>
                        <h5>File Position</h5>
                        <span>Current overall status</span>
                    </div>
                    <a href="{{ route('admin.tracking.register-report') }}" class="soft-link">Open Register</a>
                </div>
                <div class="status-board">
                    <div class="status-item pending">
                        <span>Pending</span>
                        <strong>{{ $pendingCount }}</strong>
                        <div class="progress"><div class="progress-bar" style="width: {{ $pendingPercent }}%"></div></div>
                    </div>
                    <div class="status-item progressing">
                        <span>In Movement</span>
                        <strong>{{ $inProgressCount }}</strong>
                        <div class="progress"><div class="progress-bar" style="width: {{ $progressPercent }}%"></div></div>
                    </div>
                    <div class="status-item complete">
                        <span>Completed</span>
                        <strong>{{ $completedCount }}</strong>
                        <div class="progress"><div class="progress-bar" style="width: {{ $completedPercent }}%"></div></div>
                    </div>
                </div>
                <canvas id="monthlyChart" height="90"></canvas>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="panel-card h-100">
                <div class="section-title">
                    <div>
                        <h5>Today</h5>
                        <span>{{ now()->format('d M Y') }}</span>
                    </div>
                    <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#reportModal">Reports</button>
                </div>
                <div class="today-grid">
                    <div><i class="bi bi-inbox"></i><span>Received</span><strong>{{ $todayReceived }}</strong></div>
                    <div><i class="bi bi-send"></i><span>To Court</span><strong>{{ $todayCourtDispatch }}</strong></div>
                    <div><i class="bi bi-building-check"></i><span>From Court</span><strong>{{ $todayCourtReturn }}</strong></div>
                    <div><i class="bi bi-file-earmark-plus"></i><span>New Files</span><strong>{{ $todayCasesCount }}</strong></div>
                    <div><i class="bi bi-arrow-return-left"></i><span>Returned</span><strong>{{ $returnedToLawyerCount }}</strong></div>
                    <div><i class="bi bi-shield-exclamation"></i><span>Override</span><strong>{{ $todayOverride }}</strong></div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-3">
        <div class="col-xl-5">
            <div class="panel-card h-100">
                <div class="section-title">
                    <div>
                        <h5>Movement Mix</h5>
                        <span>{{ $periodDays }} day activity, total {{ $totalPeriodMovements }}</span>
                    </div>
                </div>
                <canvas id="movementMixChart" height="150"></canvas>
                <div class="mix-legend">
                    <span>Receive {{ $periodReceive }}</span>
                    <span>Reject {{ $periodReject }}</span>
                    <span>Court {{ $periodCourtDispatch }}</span>
                    <span>Return {{ $periodCourtReturn }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="panel-card h-100">
                <div class="section-title">
                    <div>
                        <h5>Last 7 Days Flow</h5>
                        <span>Daily scan activity by movement type</span>
                    </div>
                </div>
                <canvas id="flowChart" height="120"></canvas>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-3">
        <div class="col-xl-5">
            <div class="panel-card h-100">
                <div class="section-title">
                    <div>
                        <h5>Section Backlog</h5>
                        <span>Where files are waiting now</span>
                    </div>
                </div>
                <canvas id="sectionChart" height="170"></canvas>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="panel-card h-100">
                <div class="section-title">
                    <div>
                        <h5>Oldest Active Files</h5>
                        <span>Priority files for follow-up</span>
                    </div>
                    <a class="soft-link" href="{{ route('admin.tracking.register-report') }}">Full Report</a>
                </div>
                <div class="table-responsive">
                    <table class="table dashboard-table mb-0">
                        <thead>
                            <tr>
                                <th>Case No</th>
                                <th>Petitioner</th>
                                <th>Current Desk</th>
                                <th>Age</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($oldestActiveCases as $case)
                                <tr>
                                    <td class="fw-bold">{{ $case->case_reference ?? $case->permanent_barcode ?? $case->temporary_barcode ?? ('CASE-' . $case->id) }}</td>
                                    <td>{{ $case->petitioners->first()->name_or_organization ?? '-' }}</td>
                                    <td><span class="desk-pill">{{ $case->current_section ?? 'Unassigned' }}</span></td>
                                    <td>{{ round(optional($case->created_at)->diffInDays(now())) }}d</td>
                                    <td>
                                        @if($canUseLookup)
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.tracking.timeline', $case) }}">View</a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No active files</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3">
        <div class="col-xl-8">
            <div class="panel-card h-100">
                <div class="section-title">
                    <div>
                        <h5>Recent Movement Log</h5>
                        <span>Latest barcode scan activity</span>
                    </div>
                    <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#holdersModal">Top Holders</button>
                </div>
                <div class="table-responsive">
                    <table class="table dashboard-table mb-0">
                        <thead>
                            <tr>
                                <th>Case No</th>
                                <th>Movement</th>
                                <th>Status</th>
                                <th>By</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentMovements as $movement)
                                @php
                                    $logCase = $movement->courtCase;
                                    $fileNo = $logCase?->case_reference ?? $logCase?->permanent_barcode ?? $movement->barcode_scanned;
                                    $movementText = $movementLabels[$movement->movement_type] ?? ucwords(str_replace('_', ' ', $movement->movement_type));
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $fileNo ?: '-' }}</td>
                                    <td>
                                        <span class="desk-pill">{{ $movement->from_section ?? '-' }}</span>
                                        <i class="bi bi-arrow-right-short text-primary mx-1"></i>
                                        <span class="desk-pill">{{ $movement->to_section ?? '-' }}</span>
                                    </td>
                                    <td><span class="status-pill">{{ $movementText }}</span></td>
                                    <td>{{ $movement->receivedBy?->name ?? '-' }}</td>
                                    <td>{{ optional($movement->received_at)->format('d M h:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No movement yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="panel-card h-100 quick-actions">
                <div class="section-title">
                    <div>
                        <h5>Quick Actions</h5>
                        <span>Daily admin work</span>
                    </div>
                </div>
                @if($canUseLookup)
                    <a href="{{ route('admin.tracking.lookup') }}"><i class="bi bi-search"></i><span>Find File</span></a>
                @endif
                <a href="{{ route('admin.tracking.register-report') }}"><i class="bi bi-printer"></i><span>Register Report</span></a>
                <a href="{{ route('admin.tracking.court.batches.index') }}"><i class="bi bi-collection"></i><span>Court Batches</span></a>
                <a href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i><span>Users and Lawyers</span></a>
                <a href="{{ route('admin.departments.index') }}"><i class="bi bi-diagram-3"></i><span>Departments</span></a>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Report Links</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <a class="list-group-item list-group-item-action" href="{{ route('admin.tracking.register-report', ['filter_mode' => 'date_range', 'date_from' => now()->toDateString(), 'date_to' => now()->toDateString(), 'movement_scope' => 'all']) }}">Today's Register</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('admin.tracking.register-report', ['filter_mode' => 'date_range', 'date_from' => now()->toDateString(), 'date_to' => now()->toDateString(), 'movement_scope' => 'in']) }}">Today's Received Files</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('admin.tracking.register-report', ['filter_mode' => 'date_range', 'date_from' => now()->toDateString(), 'date_to' => now()->toDateString(), 'movement_scope' => 'out']) }}">Today's Sent Files</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('admin.tracking.register-report', ['filter_mode' => 'date_range', 'date_from' => now()->toDateString(), 'date_to' => now()->toDateString(), 'movement_type' => 'dispatch_to_court']) }}">Today's Court Dispatch</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="holdersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Top Responsible Holders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>Holder</th><th>Current Files</th></tr></thead>
                        <tbody>
                            @forelse($topHolders as $holder)
                                <tr><td>{{ $holder->holder_name }}</td><td>{{ $holder->total }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">No holder data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .dashboard-page { background: #f4f7fb; max-width: 1180px; }
    .dashboard-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        border-radius: 4px;
        background: #fff;
        color: #1f2937;
        border-top: 3px solid #00284d;
        border-left: 1px solid #e3e8ef;
        border-right: 1px solid #e3e8ef;
        border-bottom: 1px solid #d4a017;
        box-shadow: 0 1px 4px rgba(0, 40, 77, .08);
    }
    .dashboard-hero h4 { font-size: 1.15rem; font-weight: 800; color: #00284d; }
    .dashboard-hero p { color: #6b7280; font-size: .9rem; }
    .system-mark { display: inline-block; font-weight: 800; letter-spacing: 0; color: #b87d08; margin-bottom: .1rem; font-size: .82rem; }
    .period-switch { display: inline-flex; gap: 0; border: 1px solid #d7dde5; border-radius: 4px; overflow: hidden; background: #fff; }
    .period-switch a { color: #374151; text-decoration: none; font-weight: 700; font-size: .82rem; padding: .4rem .7rem; border-right: 1px solid #d7dde5; }
    .period-switch a:last-child { border-right: 0; }
    .period-switch a.active { background: #00284d; color: #fff; }
    .metric-card {
        min-height: 118px;
        border-radius: 4px;
        padding: .9rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .14);
    }
    .metric-card.primary { background: #0a4f86; }
    .metric-card.warning { background: #b87d08; }
    .metric-card.info { background: #087587; }
    .metric-card.danger { background: #a93b2d; }
    .metric-card span { display: block; font-weight: 700; opacity: .94; font-size: .9rem; }
    .metric-card strong { display: block; font-size: 2rem; line-height: 1; margin: .45rem 0 .35rem; }
    .metric-card small { opacity: .86; font-size: .8rem; }
    .metric-icon {
        position: absolute;
        right: .75rem;
        top: .75rem;
        width: 2.15rem;
        height: 2.15rem;
        display: grid;
        place-items: center;
        border-radius: 4px;
        background: rgba(255,255,255,.16);
        font-size: 1.1rem;
    }
    .panel-card {
        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: 4px;
        padding: 0;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .07);
        overflow: hidden;
    }
    .panel-card > canvas,
    .panel-card > .status-board,
    .panel-card > .today-grid,
    .panel-card > .mix-legend,
    .panel-card > .table-responsive,
    .panel-card.quick-actions > a { margin-left: 1rem; margin-right: 1rem; }
    .panel-card > canvas { margin-bottom: 1rem; }
    .panel-card > .table-responsive { margin-bottom: 1rem; }
    .section-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: .75rem 1rem;
        border-top: 3px solid #00284d;
        border-bottom: 1px solid #e5e7eb;
        background: #fbfcfe;
    }
    .section-title h5 { margin: 0; color: #1f2937; font-size: 1rem; font-weight: 800; }
    .section-title span { color: #6b7280; font-size: .84rem; font-weight: 600; }
    .soft-link { color: #00284d; text-decoration: none; font-weight: 800; font-size: .88rem; }
    .soft-link:hover { color: #b87d08; }
    .status-board { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; margin-bottom: 1rem; }
    .status-item { border: 1px solid #e5e7eb; border-radius: 4px; padding: .75rem; background: #fff; }
    .status-item span { display: block; color: #6b7280; font-size: .8rem; font-weight: 800; text-transform: uppercase; }
    .status-item strong { display: block; font-size: 1.65rem; line-height: 1.1; margin: .25rem 0 .6rem; color: #111827; }
    .status-item .progress { height: .42rem; background: #e9edf2; }
    .status-item.pending .progress-bar { background: #d4a017; }
    .status-item.progressing .progress-bar { background: #087587; }
    .status-item.complete .progress-bar { background: #21854a; }
    .today-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .65rem; margin-bottom: 1rem; }
    .today-grid > div { border: 1px solid #e5e7eb; border-radius: 4px; padding: .7rem; background: #fff; min-height: 78px; }
    .today-grid i { color: #d4a017; font-size: 1.1rem; }
    .today-grid span { display: block; color: #4b5563; font-size: .82rem; font-weight: 700; margin-top: .25rem; }
    .today-grid strong { color: #00284d; font-size: 1.45rem; line-height: 1; }
    .mix-legend { display: flex; flex-wrap: wrap; gap: .45rem; margin-top: .85rem; margin-bottom: 1rem; }
    .mix-legend span, .desk-pill, .status-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 4px;
        border: 1px solid #d8e3ef;
        background: #f7fbff;
        color: #0b4f8a;
        font-size: .78rem;
        font-weight: 700;
        padding: .22rem .55rem;
        white-space: nowrap;
    }
    .status-pill { background: #fff7e6; border-color: #f3d58e; color: #805500; }
    .dashboard-table { font-size: .9rem; }
    .dashboard-table thead th { background: #eef5fb; color: #00284d; border-bottom: 0; font-weight: 800; white-space: nowrap; }
    .dashboard-table td { vertical-align: middle; color: #374151; }
    .quick-actions { display: grid; gap: .55rem; align-content: start; padding-bottom: 1rem; }
    .quick-actions .section-title { margin-bottom: .25rem; }
    .quick-actions a {
        display: flex;
        align-items: center;
        gap: .65rem;
        padding: .75rem .85rem;
        border: 1px solid #e3e8ef;
        border-radius: 4px;
        color: #1f2937;
        background: #fbfcfe;
        text-decoration: none;
        font-weight: 800;
    }
    .quick-actions a:hover { border-color: #d4a017; color: #00284d; background: #fffaf0; }
    .quick-actions i { color: #d4a017; font-size: 1.1rem; }
    @media (max-width: 767.98px) {
        .dashboard-hero { align-items: stretch; flex-direction: column; }
        .period-switch { width: 100%; }
        .period-switch a { flex: 1; text-align: center; }
        .status-board, .today-grid { grid-template-columns: 1fr; }
        .dashboard-table { min-width: 720px; }
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { labels: { boxWidth: 12, color: '#374151', font: { weight: '600' } } }
        },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#edf2f7' } },
            x: { grid: { display: false } }
        }
    };

    new Chart(document.getElementById('monthlyChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: @json($monthlyLabels),
            datasets: [{
                label: 'New Files',
                data: @json($monthlyCounts),
                borderColor: '#0b4f8a',
                backgroundColor: 'rgba(11,79,138,0.10)',
                fill: true,
                tension: .35,
                pointRadius: 3
            }]
        },
        options: { ...chartDefaults, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('movementMixChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Receive', 'Reject', 'Sent to Court', 'Court Return', 'Override'],
            datasets: [{
                data: [{{ $periodReceive }}, {{ $periodReject }}, {{ $periodCourtDispatch }}, {{ $periodCourtReturn }}, {{ $periodOverride }}],
                backgroundColor: ['#21854a', '#a93b2d', '#0a4f86', '#087587', '#d4a017'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } }, cutout: '62%' }
    });

    new Chart(document.getElementById('flowChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: @json($last7Labels),
            datasets: [
                { label: 'Receive', data: @json($last7Receive), borderColor: '#21854a', tension: .3, fill: false },
                { label: 'Reject', data: @json($last7Reject), borderColor: '#a93b2d', tension: .3, fill: false },
                { label: 'To Court', data: @json($last7CourtDispatch), borderColor: '#0a4f86', tension: .3, fill: false },
                { label: 'Court Return', data: @json($last7CourtReturn), borderColor: '#087587', tension: .3, fill: false }
            ]
        },
        options: { ...chartDefaults, plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('sectionChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: @json($sectionLabels),
            datasets: [{ label: 'Files', data: @json($sectionValues), backgroundColor: '#00284d', borderRadius: 5 }]
        },
        options: { ...chartDefaults, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } }, y: { grid: { display: false } } } }
    });
</script>
@endpush
