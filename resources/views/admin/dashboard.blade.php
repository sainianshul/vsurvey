@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="page-pretitle">Overview</div>
                <h2 class="page-title">Dashboard</h2>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row row-deck row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-primary-lt text-primary avatar avatar-md">
                                <i class="ti ti-users fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-2" id="stat-total-users">
                                <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                            </div>
                            <div class="text-secondary">Total Users</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="text-muted small mt-2 d-block">View all →</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue + Chart Row --}}
    <div class="row row-deck row-cards mb-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">User Growth (Last 30 Days)</h3>
                </div>
                <div class="card-body p-0">
                    <div id="chart-users" style="min-height: 250px;">
                        <div class="d-flex justify-content-center align-items-center h-100 py-5">
                            <div class="spinner-border text-secondary" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Tables Row --}}
    <div class="row row-deck row-cards">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Users</h3>
                    <div class="card-actions">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm">View All</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody id="table-recent-users">
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    <div class="spinner-border text-secondary" role="status"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        fetch("{{ route('admin.dashboard.stats') }}")
            .then(response => response.json())
            .then(data => {
                // Update Top Stats
                document.getElementById('stat-total-users').innerText = data.total_users;

                // Update Recent Users
                const usersTbody = document.getElementById('table-recent-users');
                if (data.recent_users && data.recent_users.length > 0) {
                    let userHtml = '';
                    data.recent_users.forEach(user => {
                        userHtml += `
                            <tr>
                                <td>
                                    <div class="d-flex py-1 align-items-center">
                                        <span class="avatar me-2 bg-primary-lt">${user.initials}</span>
                                        <div class="flex-fill">
                                            <div class="font-weight-medium"><a href="/admin/users/${user.id}" class="text-reset">${user.name}</a></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-secondary">${user.phone ?? 'N/A'}</td>
                                <td>${user.joined}</td>
                            </tr>
                        `;
                    });
                    usersTbody.innerHTML = userHtml;
                } else {
                    usersTbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">No recent users found</td></tr>';
                }

                // Render Chart
                if (typeof ApexCharts !== 'undefined') {
                    document.getElementById('chart-users').innerHTML = '';
                    new ApexCharts(document.getElementById('chart-users'), {
                        chart: {
                            type: "area",
                            fontFamily: 'inherit',
                            height: 250,
                            parentHeightOffset: 0,
                            toolbar: { show: false },
                            animations: { enabled: true }
                        },
                        dataLabels: { enabled: false },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.3,
                                opacityTo: 0.1,
                                stops: [0, 90, 100]
                            }
                        },
                        stroke: {
                            width: 2,
                            lineCap: "round",
                            curve: "smooth",
                        },
                        series: [{
                            name: "New Users",
                            data: data.chart.counts
                        }],
                        tooltip: { theme: 'dark' },
                        grid: {
                            strokeDashArray: 4,
                            padding: { top: -20, right: 0, left: -4, bottom: -4 }
                        },
                        xaxis: {
                            labels: { padding: 0 },
                            tooltip: { enabled: false },
                            axisBorder: { show: false },
                            categories: data.chart.dates
                        },
                        yaxis: {
                            labels: { padding: 4 }
                        },
                        colors: ['#206bc4']
                    }).render();
                } else {
                    document.getElementById('chart-users').innerHTML = '<div class="text-center text-danger py-4">Chart library failed to load</div>';
                }
            })
            .catch(error => {
                console.error("Error loading dashboard stats:", error);
                document.getElementById('stat-total-users').innerText = 'Error';
                document.getElementById('chart-users').innerHTML = '<div class="text-center text-danger py-4">Failed to load chart</div>';
            });
    });
</script>
@endpush