@extends('admin.layouts.app')

@section('title', 'Upload Manual Call')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Upload Manual Call Log
                </h2>
                <div class="text-muted mt-1">Submit a call recording manually for AI analysis.</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('admin.call-logs.index') }}" class="btn btn-secondary d-none d-sm-inline-block">
                        Back to Logs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.call-logs.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Agent / Caller</label>
                                <select class="form-select" name="user_id" required>
                                    <option value="" disabled selected>Select the Agent who made the call</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}">{{ $agent->name }} ({{ $agent->phone }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Voter's Phone Number</label>
                                <input type="text" class="form-control" name="phone_number" placeholder="Enter phone number" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Voter's Name (Optional)</label>
                                <input type="text" class="form-control" name="name" placeholder="Enter name">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Call Date & Time</label>
                                <input type="datetime-local" class="form-control" name="call_timing" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Call Duration (Optional)</label>
                                <input type="text" class="form-control" name="call_duration" placeholder="e.g. 2m 45s or 165">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Upload Audio (.mp3, .wav, .m4a)</label>
                                <input type="file" class="form-control" name="audio" accept=".mp3,.wav,.ogg,.m4a,.mp4" required>
                                <small class="form-hint">Max file size: 10MB</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1" /><path d="M9 15l3 -3l3 3" /><path d="M12 12l0 9" /></svg>
                            Upload & Analyze Call
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center" id="loadingDiv" style="display: none;">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-muted">Analyzing audio... This may take up to 2-3 minutes. Please do not close this page.</div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('uploadForm').addEventListener('submit', function() {
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('loadingDiv').style.display = 'block';
    });
</script>
@endsection
