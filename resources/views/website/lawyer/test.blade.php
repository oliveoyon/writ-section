@extends('website.layouts.adminlayout')

@section('content')
<style>
    .dashboard-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        padding: 20px;
        margin-bottom: 20px;
    }

    .chart-container {
        position: relative;
        height: 300px;
    }
</style>

<div class="container py-4">

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="dashboard-card bg-primary text-white text-center">
                <h5>Total Writ Files</h5>
                <h2>125</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card bg-success text-white text-center">
                <h5>Pending</h5>
                <h2>35</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card bg-warning text-dark text-center">
                <h5>In Progress</h5>
                <h2>60</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card bg-danger text-white text-center">
                <h5>Completed</h5>
                <h2>30</h2>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Writ Files by Month (Bar Chart) -->
        <div class="col-md-6">
            <div class="dashboard-card">
                <h5>Writ Files by Month</h5>
                <div class="chart-container">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Status Distribution (Pie Chart) -->
        <div class="col-md-6">
            <div class="dashboard-card">
                <h5>Status Distribution</h5>
                <div class="chart-container">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Writ Files Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="dashboard-card">
                <h5>Recent Writ Files</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>File No</th>
                                <th>Client Name</th>
                                <th>Filed Date</th>
                                <th>Status</th>
                                <th>Assigned Lawyer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>W-001</td>
                                <td>John Doe</td>
                                <td>2025-10-01</td>
                                <td>Pending</td>
                                <td>Lawyer A</td>
                            </tr>
                            <tr>
                                <td>W-002</td>
                                <td>Jane Smith</td>
                                <td>2025-10-05</td>
                                <td>In Progress</td>
                                <td>Lawyer B</td>
                            </tr>
                            <tr>
                                <td>W-003</td>
                                <td>David Johnson</td>
                                <td>2025-10-07</td>
                                <td>Completed</td>
                                <td>Lawyer C</td>
                            </tr>
                            <tr>
                                <td>W-004</td>
                                <td>Mary Lee</td>
                                <td>2025-10-10</td>
                                <td>In Progress</td>
                                <td>Lawyer A</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Bar Chart
    const barCtx = document.getElementById('barChart').getContext('2d');
    const barChart = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Writ Files',
                data: [12, 19, 7, 15, 22, 10],
                backgroundColor: '#00284d'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Pie Chart
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    const pieChart = new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: ['Pending', 'In Progress', 'Completed'],
            datasets: [{
                label: 'Status',
                data: [35, 60, 30],
                backgroundColor: ['#ffc107', '#28a745', '#dc3545']
            }]
        },
        options: {
            responsive: true
        }
    });
</script>
@endsection
