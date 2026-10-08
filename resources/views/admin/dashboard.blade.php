@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="page-pretitle">Overview</div>
                <h2 class="page-title">Survey Dashboard</h2>
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
                                <i class="ti ti-phone fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-2" id="stat-total-calls">
                                <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                            </div>
                            <div class="text-secondary">Total Call Logs</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-info-lt text-info avatar avatar-md">
                                <i class="ti ti-calendar fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-2" id="stat-today-calls">
                                <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                            </div>
                            <div class="text-secondary">Today's Calls</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-success-lt text-success avatar avatar-md">
                                <i class="ti ti-mood-smile fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-2" id="stat-positive-calls">
                                <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                            </div>
                            <div class="text-secondary">Positive Sentiment</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-danger-lt text-danger avatar avatar-md">
                                <i class="ti ti-mood-sad fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-2" id="stat-negative-calls">
                                <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                            </div>
                            <div class="text-secondary">Negative Sentiment</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart Row --}}
    <div class="row row-deck row-cards mb-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">Survey Calls (Last 30 Days)</h3>
                </div>
                <div class="card-body p-0">
                    <div id="chart-calls" style="min-height: 250px;">
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
                    <h3 class="card-title">Recent Survey Calls</h3>
                    <div class="card-actions">
                        <a href="{{ route('admin.call-logs.index') }}" class="btn btn-sm">View All Logs</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Voter Number</th>
                                <th>Agent</th>
                                <th>Sentiment</th>
                                <th>Timing</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="table-recent-calls">
                            <tr>
                                <td colspan="5" class="text-center py-4">
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
                document.getElementById('stat-total-calls').innerText = data.total_calls;
                document.getElementById('stat-today-calls').innerText = data.today_calls;
                document.getElementById('stat-positive-calls').innerText = data.positive_calls;
                document.getElementById('stat-negative-calls').innerText = data.negative_calls;

                // Update Recent Calls
                const callsTbody = document.getElementById('table-recent-calls');
                if (data.recent_calls && data.recent_calls.length > 0) {
                    let callsHtml = '';
                    data.recent_calls.forEach(call => {
                        let icon = call.sentiment.toLowerCase() === 'positive' ? 'ti-mood-smile' : (call.sentiment.toLowerCase() === 'negative' ? 'ti-mood-sad' : 'ti-mood-empty');
                        callsHtml += `
                            <tr>
                                <td class="fw-semibold text-primary">${call.phone ?? 'N/A'}</td>
                                <td>${call.agent}</td>
                                <td>
                                    <span class="badge badge-outline rounded-0 text-${call.sentiment_color} fs-5">
                                        <i class="ti ${icon} me-1"></i>${call.sentiment}
                                    </span>
                                </td>
                                <td>${call.timing}</td>
                                <td>
                                    <a href="/admin/call-logs/${call.id}" class="btn btn-icon btn-sm btn-outline-primary rounded-0">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                    callsTbody.innerHTML = callsHtml;
                } else {
                    callsTbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No recent calls found</td></tr>';
                }

                // Render Chart
                if (typeof ApexCharts !== 'undefined') {
                    document.getElementById('chart-calls').innerHTML = '';
                    new ApexCharts(document.getElementById('chart-calls'), {
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
                            name: "Survey Calls",
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
                    document.getElementById('chart-calls').innerHTML = '<div class="text-center text-danger py-4">Chart library failed to load</div>';
                }
            })
            .catch(error => {
                console.error("Error loading dashboard stats:", error);
                document.getElementById('stat-total-calls').innerText = 'Error';
                document.getElementById('stat-today-calls').innerText = 'Error';
                document.getElementById('stat-positive-calls').innerText = 'Error';
                document.getElementById('stat-negative-calls').innerText = 'Error';
                document.getElementById('chart-calls').innerHTML = '<div class="text-center text-danger py-4">Failed to load chart</div>';
            });
    });
</script>
@endpush