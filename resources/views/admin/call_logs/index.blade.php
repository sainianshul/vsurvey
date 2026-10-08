@extends('admin.layouts.app')

@section('title', 'Survey Call Logs')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Survey Call Logs</h2>
                <div class="text-muted mt-1">Manage and view all automated and manual call logs</div>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.call-logs.create') }}" class="btn btn-primary">
                    <i class="ti ti-upload me-1"></i>Upload Manual Call
                </a>
            </div>
        </div>
    </div>

    <div class="card">

        {{-- Toolbar --}}
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-3">

                {{-- Search --}}
                <div class="input-icon">
                    <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                    <input type="text" id="dt-search" class="form-control" style="width: 260px;"
                        placeholder="Search voter number..." />
                </div>

                {{-- Right Controls --}}
                <div class="d-flex align-items-center gap-2">

                    {{-- Refresh Button --}}
                    <button type="button" class="btn btn-icon btn-ghost-secondary" id="refresh-table-btn"
                        data-bs-toggle="tooltip" title="Refresh">
                        <i class="ti ti-refresh"></i>
                    </button>

                    {{-- Sentiment Filter --}}
                    <select id="filter-sentiment" class="form-select" style="width: 150px;">
                        <option value="">All Sentiments</option>
                        <option value="positive">Positive</option>
                        <option value="negative">Negative</option>
                        <option value="neutral">Neutral</option>
                    </select>

                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">

            {{-- Loading Spinner --}}
            <div id="logs-loader" class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            {{-- Table --}}
            <div id="logs-table-wrapper" class="table-responsive d-none">
                <table id="logs-table" class="table table-vcenter w-100">
                    <thead>
                        <tr>
                            <th class="w-1">S.No</th>
                            <th>Caller / Agent</th>
                            <th>Voter Number</th>
                            <th>Date & Time</th>
                            <th>Duration</th>
                            <th>Sentiment</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            {{-- Empty State --}}
            <div id="logs-empty" class="empty d-none">
                <div class="empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" /></svg>
                </div>
                <p class="empty-title">No call logs found</p>
                <p class="empty-subtitle text-muted">
                    Try adjusting your search or filters to find what you are looking for.
                </p>
            </div>

        </div>
    </div>

@endsection

@push('datatables_css')
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@push('datatables_js')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(function () {
            let table = $('#logs-table').DataTable({
                serverSide: true,
                processing: false,
                ajax: {
                    url: '{{ route('admin.call-logs.index') }}',
                    data: function (d) {
                        d.sentiment = $('#filter-sentiment').val();
                    }
                },
                columns: [
                    { data: null, name: 'id', render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false },
                    { data: 'user_id', name: 'user_id', orderable: false, searchable: false },
                    { data: 'phone_number', name: 'phone_number' },
                    { data: 'call_timing', name: 'call_timing', searchable: false },
                    { data: 'call_duration', name: 'call_duration', orderable: false, searchable: false },
                    { data: 'sentiment', name: 'sentiment', searchable: false },
                    { data: 'is_success', name: 'is_success', searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                ],
                order: [[3, 'desc']], // Order by call_timing desc
                pageLength: 15,
                lengthMenu: [[10, 15, 25, 50], [10, 15, 25, 50]],
                dom:
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-3 pt-3 flex-nowrap'" +
                    "<'col-sm-12 col-md-5'i>" +
                    "<'col-sm-12 col-md-7 d-flex justify-content-md-end align-items-center gap-3'lp>>",
                language: {
                    emptyTable: ' ',
                    zeroRecords: ' ',
                    loadingRecords: ' ',
                    info: 'Showing _START_–_END_ of _TOTAL_ calls',
                    infoEmpty: 'No calls to show',
                    infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    },
                },
                initComplete: function () {
                    $('#logs-loader').remove();
                    let total = this.api().page.info().recordsDisplay;
                    if (total === 0) {
                        $('#logs-table-wrapper').addClass('d-none');
                        $('#logs-empty').removeClass('d-none');
                    } else {
                        $('#logs-empty').addClass('d-none');
                        $('#logs-table-wrapper').removeClass('d-none');
                    }
                },
                drawCallback: function () {
                    if ($('#logs-loader').length === 0) {
                        let total = this.api().page.info().recordsDisplay;
                        if (total === 0) {
                            $('#logs-table-wrapper').addClass('d-none');
                            $('#logs-empty').removeClass('d-none');
                        } else {
                            $('#logs-empty').addClass('d-none');
                            $('#logs-table-wrapper').removeClass('d-none');
                        }
                    }
                    $('[data-bs-toggle="tooltip"]').tooltip({ trigger: 'hover' });
                }
            });

            // Search
            let searchTimer;
            $('#dt-search').on('input', function () {
                clearTimeout(searchTimer);
                let query = $(this).val();
                searchTimer = setTimeout(function () {
                    table.search(query).draw();
                }, 400);
            });

            // Status Filter
            $('#filter-sentiment').on('change', function () {
                table.ajax.reload();
            });

            // Refresh Button
            $('#refresh-table-btn').on('click', function () {
                $(this).tooltip('hide');
                table.ajax.reload(null, false);
                Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Refreshed' });
            });
        });
    </script>
@endpush
