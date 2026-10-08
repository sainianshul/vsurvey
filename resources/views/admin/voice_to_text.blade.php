@extends('admin.layouts.app')
@section('title', 'Voice to Text (Survey)')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="page-pretitle">AI Services</div>
                <h2 class="page-title">Voice to Text Transcription</h2>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Upload Audio File</h3>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('admin.voice-to-text.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Audio File (MP3, WAV, M4A, OGG)</label>
                            <input type="file" name="audio" class="form-control" accept="audio/*" required>
                            @error('audio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-hint text-muted mt-2">
                                <i class="ti ti-info-circle"></i> Max 10MB limit. File will be sent to the local Vosk AI Microservice.
                            </small>
                        </div>
                        <div class="form-footer mt-4">
                            <button type="submit" class="btn btn-primary w-100" id="upload-btn">
                                <i class="ti ti-wand me-2"></i> Convert to Text
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Transcription Result</h3>
                </div>
                <div class="card-body">
                    @if (session('json_result'))
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-success-lt p-2"><i class="ti ti-check me-1"></i> Success</span>
                            <span class="ms-auto text-muted">File: <strong>{{ session('json_result')['filename'] ?? 'Unknown' }}</strong></span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Hindi Text (Transcribed):</label>
                            <div class="form-control" style="min-height: 100px; background-color: #f8f9fa; font-size: 16px;">
                                {{ session('json_result')['text'] ?? 'No text recognized.' }}
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Raw JSON Response:</label>
                            <pre class="bg-dark text-light p-3 rounded"><code>{{ json_encode(session('json_result'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        </div>
                    @else
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="ti ti-microphone-2 fs-1 text-muted"></i>
                            </div>
                            <p class="empty-title">No Audio Processed Yet</p>
                            <p class="empty-subtitle text-muted">
                                Upload an audio file on the left and click "Convert to Text" to see the transcription result here.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.getElementById('upload-btn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Transcribing AI Model...';
        btn.classList.add('disabled');
    });
</script>
@endpush
