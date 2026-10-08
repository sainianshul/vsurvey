@extends('admin.layouts.app')

@section('title', 'Call Analysis')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <x-breadcrumb :items="[
                ['label' => 'Survey Calls', 'url' => route('admin.call-logs.index')],
                ['label' => 'Call Analysis'],
            ]" />
            <h2 class="page-title">
                Call Analysis & Insights
            </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <a href="{{ route('admin.call-logs.index') }}" class="btn btn-secondary d-none d-sm-inline-block">
                    <i class="ti ti-arrow-left me-1"></i> Back to Logs
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    
    @php
        $sentiment = strtolower($callLog->sentiment);
        if ($sentiment == 'positive') {
            $sentimentColor = 'success';
            $sentimentIcon = 'ti-mood-smile';
            $sentimentText = 'Positive';
        } elseif ($sentiment == 'negative') {
            $sentimentColor = 'danger';
            $sentimentIcon = 'ti-mood-sad';
            $sentimentText = 'Negative';
        } else {
            $sentimentColor = 'secondary';
            $sentimentIcon = 'ti-mood-empty';
            $sentimentText = 'Neutral';
        }
        $analysis = $callLog->ai_response['analysis'] ?? [];
    @endphp

    <!-- Overview Stats Row -->
    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-primary text-white avatar"><i class="ti ti-phone fs-2"></i></span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium">Voter Number</div>
                            <div class="text-muted">{{ $callLog->phone_number }}</div>
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
                            <span class="bg-info text-white avatar"><i class="ti ti-headset fs-2"></i></span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium">Agent</div>
                            <div class="text-muted">{{ $callLog->user->name ?? 'System/Webhook' }}</div>
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
                            <span class="bg-{{ $sentimentColor }} text-white avatar"><i class="ti {{ $sentimentIcon }} fs-2"></i></span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium">Sentiment</div>
                            <div class="text-{{ $sentimentColor }} fw-bold">{{ $sentimentText }}</div>
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
                            <span class="bg-warning text-white avatar"><i class="ti ti-clock fs-2"></i></span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium">Timing & Duration</div>
                            <div class="text-muted">{{ $callLog->call_timing ? $callLog->call_timing->format('d M Y, H:i') : $callLog->created_at->format('d M Y, H:i') }} ({{ $callLog->call_duration ?? 'Unknown' }})</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        
        <!-- Left Column: Insights -->
        <div class="col-lg-6">
            
            <div class="card mb-3">
                <div class="card-header bg-green-lt">
                    <h3 class="card-title text-green"><i class="ti ti-message-report me-2"></i>Call Summary / Feedback</h3>
                </div>
                <div class="card-body">
                    @if(!empty($analysis['feedback']))
                        <p class="mb-0 text-dark" style="font-size: 1.05rem; line-height: 1.6;">
                            {{ is_array($analysis['feedback']) ? implode(', ', $analysis['feedback']) : $analysis['feedback'] }}
                        </p>
                    @else
                        <span class="text-muted">No explicit summary/feedback given.</span>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-blue-lt">
                    <h3 class="card-title text-blue"><i class="ti ti-list-check me-2"></i>Key Points</h3>
                </div>
                <div class="card-body">
                    @if(!empty($analysis['key_points']))
                        <ul class="mb-0 text-dark" style="font-size: 1.05rem; line-height: 1.6;">
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
                    <h3 class="card-title text-red"><i class="ti ti-alert-triangle me-2"></i>Complaints & Issues</h3>
                </div>
                <div class="card-body">
                    @if(!empty($analysis['complaints']))
                        <ul class="mb-0" style="font-size: 1.05rem; line-height: 1.6; color: #d63939;">
                            @foreach($analysis['complaints'] as $complaint)
                                <li><strong>{{ $complaint }}</strong></li>
                            @endforeach
                        </ul>
                    @else
                        <span class="text-success"><i class="ti ti-check me-1"></i> No complaints detected in this call.</span>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-yellow-lt">
                    <h3 class="card-title text-yellow"><i class="ti ti-tags me-2"></i>Important Keywords</h3>
                </div>
                <div class="card-body">
                    @if(!empty($analysis['keywords']))
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($analysis['keywords'] as $keyword)
                                <span class="badge badge-outline text-yellow rounded-0 px-2 py-1 fs-5">{{ $keyword }}</span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted">No keywords detected.</span>
                    @endif
                </div>
            </div>

        </div>

        <!-- Right Column: Transcript -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-quote me-2"></i>Full Call Transcript</h3>
                </div>
                <div class="card-body p-4 bg-light" style="max-height: 800px; overflow-y: auto;">
                    @if($callLog->audio_text)
                        @php
                            $lines = explode("\n", $callLog->audio_text);
                        @endphp
                        @foreach($lines as $line)
                            @if(trim($line) != '')
                                @if(str_contains(strtolower($line), 'speaker 1:'))
                                    <div class="mb-3">
                                        <div class="text-primary fw-bold mb-1">Speaker 1 (Agent):</div>
                                        <div class="bg-white p-3 border rounded shadow-sm">{{ str_replace(['Speaker 1:', 'speaker 1:'], '', $line) }}</div>
                                    </div>
                                @elseif(str_contains(strtolower($line), 'speaker 2:'))
                                    <div class="mb-3 text-end">
                                        <div class="text-success fw-bold mb-1">Speaker 2 (Voter):</div>
                                        <div class="bg-success-lt p-3 border rounded shadow-sm d-inline-block text-start">{{ str_replace(['Speaker 2:', 'speaker 2:'], '', $line) }}</div>
                                    </div>
                                @else
                                    <p style="font-size: 1.05rem; line-height: 1.6; color: #333;">{{ $line }}</p>
                                @endif
                            @endif
                        @endforeach
                    @else
                        <div class="empty">
                            <div class="empty-icon"><i class="ti ti-microphone-off fs-1 text-muted"></i></div>
                            <p class="empty-title text-muted mt-3">No transcription available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
