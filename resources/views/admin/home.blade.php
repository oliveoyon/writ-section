@extends('admin.layouts.adminlayout')

@section('content')
<style>
    .cockpit-card { border: 0; border-radius: 14px; box-shadow: 0 10px 24px rgba(0, 40, 77, 0.08); background: #fff; }
    .kpi { border-radius: 14px; padding: 16px; color: #fff; min-height: 112px; position: relative; overflow: hidden; }
    .kpi h6 { margin: 0 0 6px; font-weight: 700; font-size: .95rem; }
    .kpi h2 { margin: 0; font-weight: 800; }
    .kpi small { opacity: .9; }
    .kpi a { color: #fff; text-decoration: none; font-weight: 700; }
    .kpi::after { content: ""; position: absolute; width: 110px; height: 110px; right: -24px; bottom: -30px; border-radius: 50%; background: rgba(255,255,255,.14); }
    .kpi-navy { background: linear-gradient(135deg, #0b4f8a, #00284d); }
    .kpi-amber { background: linear-gradient(135deg, #d49f11, #b17b00); }
    .kpi-cyan { background: linear-gradient(135deg, #0f8a9d, #0b6270); }
    .kpi-green { background: linear-gradient(135deg, #21854a, #145b31); }
    .kpi-red { background: linear-gradient(135deg, #c03d2c, #8f291d); }
    .kpi-click { cursor: pointer; transition: transform .15s ease, box-shadow .15s ease; }
    .kpi-click:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(0,0,0,.16); }
    .chip { background: #f2f7fc; border: 1px solid #d9e6f3; color: #00284d; border-radius: 20px; font-size: 13px; padding: 6px 10px; font-weight: 700; display: inline-block; margin: 4px 6px 4px 0; }
    .mini-link { color: #00284d; font-weight: 700; text-decoration: none; }
    .mini-link:hover { text-decoration: underline; }
    .table-sm td, .table-sm th { vertical-align: middle; }
    .table-head-strong th { background: #eef5fb; color: #00284d; font-weight: 800; }
    .flow-pill { display: inline-flex; align-items: center; gap: 6px; padding: 3px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; border: 1px solid #d8e3ef; background: #f7fbff; color: #0b4f8a; }
    .move-type { font-size: 12px; font-weight: 700; padding: 4px 8px; border-radius: 999px; background: #fff7e6; color: #8a5a00; border: 1px solid #f5dfac; }
</style>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-1">Tracking Cockpit</h4>
            <small class="text-muted">At-a-glance operational control for Registrar/Admin</small>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-sm {{ $periodDays === 7 ? 'btn-dark' : 'btn-outline-dark' }}" href="{{ route('admin.dashboard', ['period' => 7]) }}">7D</a>
            <a class="btn btn-sm {{ $periodDays === 30 ? 'btn-dark' : 'btn-outline-dark' }}" href="{{ route('admin.dashboard', ['period' => 30]) }}">30D</a>
            <a class="btn btn-sm {{ $periodDays === 90 ? 'btn-dark' : 'btn-outline-dark' }}" href="{{ route('admin.dashboard', ['period' => 90]) }}">90D</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-2 col-lg-3 col-sm-6"><div class="kpi kpi-navy"><h6>Total Cases</h6><h2>{{ $totalCases }}</h2></div></div>
        <div class="col-xl-2 col-lg-3 col-sm-6"><div class="kpi kpi-amber"><h6>Pending</h6><h2>{{ $pendingCount }}</h2></div></div>
        <div class="col-xl-2 col-lg-3 col-sm-6"><div class="kpi kpi-cyan"><h6>In Progress</h6><h2>{{ $inProgressCount }}</h2></div></div>
        <div class="col-xl-2 col-lg-3 col-sm-6"><div class="kpi kpi-green"><h6>Completed</h6><h2>{{ $completedCount }}</h2></div></div>
        <div class="col-xl-2 col-lg-3 col-sm-6"><div class="kpi kpi-red"><h6>Overdue 15+ Days</h6><h2>{{ $overdueCount }}</h2></div></div>
        <div class="col-xl-2 col-lg-3 col-sm-6">
            <div class="kpi kpi-navy">
                <h6>In Court</h6>
                <h2>{{ $inCourtCount }}</h2>
                <small><a href="{{ route('admin.tracking.register-report', ['filter_mode' => 'date_range', 'date_from' => now()->subDays(30)->toDateString(), 'date_to' => now()->toDateString(), 'movement_type' => 'dispatch_to_court']) }}">Drill Down</a></small>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-sm-6">
            <div class="kpi kpi-cyan kpi-click" data-bs-toggle="modal" data-bs-target="#reportModal">
                <h6>Today's Cases</h6>
                <h2>{{ $todayCasesCount }}</h2>
                <small><i class="bi bi-bar-chart-line-fill"></i> Report Links</small>
            </div>
        </div>
    </div>

    <div class="cockpit-card p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-2">Today Ops Snapshot</h6>
            <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#opsModal">Details</button>
        </div>
        <span class="chip">Received: {{ $todayReceived }}</span>
        <span class="chip">Rejected: {{ $todayRejected }}</span>
        <span class="chip">Court Dispatch: {{ $todayCourtDispatch }}</span>
        <span class="chip">Court Return: {{ $todayCourtReturn }}</span>
        <span class="chip">Override: {{ $todayOverride }}</span>
        <span class="chip">Pending Temp: {{ $pendingTempCount }}</span>
        <span class="chip">Returned To Lawyer: {{ $returnedToLawyerCount }}</span>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-8"><div class="cockpit-card p-3 h-100"><h6>Case Intake Trend (12 Months)</h6><canvas id="monthlyChart" height="110"></canvas></div></div>
        <div class="col-lg-4"><div class="cockpit-card p-3 h-100"><h6>Movement Mix ({{ $periodDays }} Days)</h6><canvas id="movementMixChart" height="110"></canvas><div class="small text-muted mt-2">Total movements: {{ $totalPeriodMovements }}</div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6"><div class="cockpit-card p-3 h-100"><h6>Last 7 Days Movement Flow</h6><canvas id="flowChart" height="110"></canvas></div></div>
        <div class="col-lg-6"><div class="cockpit-card p-3 h-100"><h6>Current Section Backlog</h6><canvas id="sectionChart" height="110"></canvas></div></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="cockpit-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Attention Needed: Oldest Active Files</h6>
                    <a class="mini-link" href="{{ route('admin.tracking.register-report') }}">Open Register</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead><tr class="table-head-strong"><th>File</th><th>Petitioner</th><th>Section</th><th>Age</th><th></th></tr></thead>
                        <tbody>
                            @forelse($oldestActiveCases as $case)
                                <tr>
                                    <td>{{ $case->final_case_number ?? $case->permanent_barcode ?? $case->temporary_barcode ?? ('CASE-' . $case->id) }}</td>
                                    <td>{{ $case->petitioners->first()->name_or_organization ?? '-' }}</td>
                                    <td><span class="flow-pill">{{ $case->current_section ?? 'Unassigned' }}</span></td>
                                    <td>{{ round(optional($case->created_at)->diffInDays(now())) }}d</td>
                                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.tracking.timeline', $case) }}">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No active files</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="cockpit-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Recent Movement Log</h6>
                    <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#holdersModal">Top Holders</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead><tr class="table-head-strong"><th>File</th><th>From -> To</th><th>Type</th><th>By</th><th>Time</th></tr></thead>
                        <tbody>
                            @forelse($recentMovements as $movement)
                                @php $logCase = $movement->courtCase; $fileNo = $logCase?->final_case_number ?? $logCase?->permanent_barcode ?? $movement->barcode_scanned; @endphp
                                <tr>
                                    <td>{{ $fileNo ?: '-' }}</td>
                                    <td>
                                        <span class="flow-pill">{{ $movement->from_section ?? '-' }}</span>
                                        <i class="bi bi-arrow-right-circle-fill text-primary"></i>
                                        <span class="flow-pill">{{ $movement->to_section ?? '-' }}</span>
                                    </td>
                                    <td><span class="move-type">{{ $movement->movement_type }}</span></td>
                                    <td>{{ $movement->receivedBy?->name ?? '-' }}</td>
                                    <td>{{ optional($movement->received_at)->format('d M h:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No movement yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Quick Report Links</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="list-group">
                    <a class="list-group-item list-group-item-action" href="{{ route('admin.tracking.register-report', ['filter_mode' => 'date_range', 'date_from' => now()->toDateString(), 'date_to' => now()->toDateString(), 'movement_scope' => 'all']) }}">Today's Register (All)</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('admin.tracking.register-report', ['filter_mode' => 'date_range', 'date_from' => now()->toDateString(), 'date_to' => now()->toDateString(), 'movement_scope' => 'in']) }}">Today's IN Register</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('admin.tracking.register-report', ['filter_mode' => 'date_range', 'date_from' => now()->toDateString(), 'date_to' => now()->toDateString(), 'movement_scope' => 'out']) }}">Today's OUT Register</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('admin.tracking.register-report', ['filter_mode' => 'date_range', 'date_from' => now()->toDateString(), 'date_to' => now()->toDateString(), 'movement_type' => 'dispatch_to_court']) }}">Today's Court Dispatch</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="opsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Operational Insight ({{ $periodDays }} Days)</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-md-4"><div class="chip w-100">Receive: {{ $periodReceive }}</div></div>
                    <div class="col-md-4"><div class="chip w-100">Reject: {{ $periodReject }}</div></div>
                    <div class="col-md-4"><div class="chip w-100">Override: {{ $periodOverride }}</div></div>
                    <div class="col-md-6"><div class="chip w-100">Dispatch To Court: {{ $periodCourtDispatch }}</div></div>
                    <div class="col-md-6"><div class="chip w-100">Returned From Court: {{ $periodCourtReturn }}</div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="holdersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Top Responsible Holders</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('monthlyChart').getContext('2d'), {
        type: 'line',
        data: { labels: @json($monthlyLabels), datasets: [{ data: @json($monthlyCounts), borderColor: '#0b4f8a', backgroundColor: 'rgba(11,79,138,0.10)', fill: true, tension: .35, pointRadius: 3 }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
    new Chart(document.getElementById('movementMixChart').getContext('2d'), {
        type: 'doughnut',
        data: { labels: ['Receive', 'Reject', 'Court Dispatch', 'Court Return', 'Override'], datasets: [{ data: [{{ $periodReceive }}, {{ $periodReject }}, {{ $periodCourtDispatch }}, {{ $periodCourtReturn }}, {{ $periodOverride }}], backgroundColor: ['#1c8c4d', '#c03d2c', '#0b4f8a', '#0d8b9e', '#d49f11'] }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
    new Chart(document.getElementById('flowChart').getContext('2d'), {
        type: 'line',
        data: { labels: @json($last7Labels), datasets: [
            { label: 'Receive', data: @json($last7Receive), borderColor: '#1c8c4d', tension: .3, fill: false },
            { label: 'Reject', data: @json($last7Reject), borderColor: '#c03d2c', tension: .3, fill: false },
            { label: 'To Court', data: @json($last7CourtDispatch), borderColor: '#0b4f8a', tension: .3, fill: false },
            { label: 'Court Return', data: @json($last7CourtReturn), borderColor: '#0d8b9e', tension: .3, fill: false }
        ]},
        options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
    new Chart(document.getElementById('sectionChart').getContext('2d'), {
        type: 'bar',
        data: { labels: @json($sectionLabels), datasets: [{ data: @json($sectionValues), backgroundColor: '#00284d' }] },
        options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
</script>
@endsection

