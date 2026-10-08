@extends('admin.layouts.app')
@section('title', 'AI Request Logs & Stats')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="page-pretitle">Monitoring</div>
                <h2 class="page-title">AI Processing Logs</h2>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent AI Requests</h3>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Filename</th>
                        <th>Model Used</th>
                        <th>Sentiment</th>
                        <th>Status</th>
                        <th>Date & Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td><span class="text-muted">{{ $log->id }}</span></td>
                            <td>{{ $log->filename ?? 'Webhook Call' }}</td>
                            <td>
                                @if($log->model_used && $log->model_used != 'none')
                                    <span class="badge bg-primary text-white">{{ $log->model_used }}</span>
                                @else
                                    <span class="badge bg-secondary">Unknown</span>
                                @endif
                            </td>
                            <td>
                                @if(strtolower($log->sentiment) == 'positive')
                                    <span class="badge bg-success">Positive</span>
                                @elseif(strtolower($log->sentiment) == 'negative')
                                    <span class="badge bg-danger">Negative</span>
                                @else
                                    <span class="badge bg-secondary">{{ $log->sentiment ?? 'N/A' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($log->is_success)
                                    <span class="text-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                        Success
                                    </span>
                                @else
                                    <span class="text-danger" title="{{ $log->error_message }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                                        Failed
                                    </span>
                                @endif
                            </td>
                            <td>
                                {{ $log->created_at->format('d M, h:i A') }}
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                    View Details
                                </button>
                                
                                <!-- Modal -->
                                <div class="modal modal-blur fade" id="logModal{{ $log->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">AI Log Details #{{ $log->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                @if(!$log->is_success)
                                                    <div class="alert alert-danger">{{ $log->error_message }}</div>
                                                @endif
                                                <div class="mb-3">
                                                    <label class="form-label">Raw JSON Response</label>
                                                    <pre class="bg-dark text-white p-3 rounded" style="white-space: pre-wrap;">{{ json_encode($log->full_response, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No AI logs found. Start processing calls to see stats here!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-footer d-flex align-items-center">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

@endsection
