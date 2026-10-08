@extends('admin.layouts.app')

@section('title', 'Call Analysis')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Call Analysis Results
                </h2>
                <div class="text-muted mt-1">Detailed AI analysis and transcription of the call.</div>
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
        
        <div class="row row-cards">
            
            <!-- Left Column: Details & Transcript -->
            <div class="col-lg-6">
                
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Call Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Agent Name</div>
                                <div class="datagrid-content">{{ $callLog->user->name ?? 'System/Webhook' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Voter Number</div>
                                <div class="datagrid-content">{{ $callLog->phone_number }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Call Timing</div>
                                <div class="datagrid-content">{{ $callLog->call_timing ? $callLog->call_timing->format('d M Y, h:i A') : $callLog->created_at->format('d M Y, h:i A') }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">AI Model Used</div>
                                <div class="datagrid-content"><span class="badge bg-purple">{{ $callLog->model_used }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Hindi Transcription</h3>
                    </div>
                    <div class="card-body">
                        @if($callLog->audio_text)
                            <p style="font-size: 1.1rem; line-height: 1.6; color: #333;">
                                {{ $callLog->audio_text }}
                            </p>
                        @else
                            <div class="alert alert-warning">No transcription available.</div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Right Column: AI Insights -->
            <div class="col-lg-6">
                
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <strong>Sentiment:</strong>
                            </div>
                            <div>
                                @php $sentiment = strtolower($callLog->sentiment); @endphp
                                @if($sentiment == 'positive')
                                    <span class="badge bg-success" style="font-size: 1rem; padding: 10px 15px;">Positive</span>
                                @elseif($sentiment == 'negative')
                                    <span class="badge bg-danger" style="font-size: 1rem; padding: 10px 15px;">Negative</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 1rem; padding: 10px 15px;">{{ ucfirst($callLog->sentiment ?? 'Neutral') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $analysis = $callLog->ai_response['analysis'] ?? [];
                @endphp

                <div class="card mb-3">
                    <div class="card-header bg-blue-lt">
                        <h3 class="card-title text-blue">Key Points Detected</h3>
                    </div>
                    <div class="card-body">
                        @if(!empty($analysis['key_points']))
                            <ul class="mb-0" style="font-size: 1.05rem; line-height: 1.6;">
                                @foreach($analysis['key_points'] as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">No key points detected.</span>
                        @endif
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-red-lt">
                        <h3 class="card-title text-red">Complaints</h3>
                    </div>
                    <div class="card-body">
                        @if(!empty($analysis['complaints']))
                            <ul class="mb-0" style="font-size: 1.05rem; line-height: 1.6; color: #d63939;">
                                @foreach($analysis['complaints'] as $complaint)
                                    <li><strong>{{ $complaint }}</strong></li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-success"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg> No complaints detected in this call.</span>
                        @endif
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-green-lt">
                        <h3 class="card-title text-green">Feedback</h3>
                    </div>
                    <div class="card-body">
                        @if(!empty($analysis['feedback']))
                            <p class="mb-0" style="font-size: 1.05rem; line-height: 1.6;">
                                {{ is_array($analysis['feedback']) ? implode(', ', $analysis['feedback']) : $analysis['feedback'] }}
                            </p>
                        @else
                            <span class="text-muted">No explicit feedback given.</span>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-yellow-lt">
                        <h3 class="card-title text-yellow">Important Keywords</h3>
                    </div>
                    <div class="card-body">
                        @if(!empty($analysis['keywords']))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($analysis['keywords'] as $keyword)
                                    <span class="badge bg-yellow text-yellow-fg" style="font-size: 0.9rem;">{{ $keyword }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">No keywords detected.</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <!-- Raw JSON Data (For debugging) -->
        <div class="card mt-4">
            <div class="card-header" data-bs-toggle="collapse" data-bs-target="#rawJson" style="cursor: pointer;">
                <h3 class="card-title">View Raw AI JSON Response (Debug)</h3>
            </div>
            <div class="card-body collapse" id="rawJson">
                <pre><code>{{ json_encode($callLog->ai_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
            </div>
        </div>

    </div>
</div>
@endsection
