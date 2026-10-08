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
            <h2 class="page-title">Call Analysis & Insights</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <a href="{{ route('admin.call-logs.index') }}" class="btn btn-secondary d-none d-sm-inline-block">
                <i class="ti ti-arrow-left me-1"></i> Back to Logs
            </a>
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
            $sentimentBg = 'bg-green-lt';
        } elseif ($sentiment == 'negative') {
            $sentimentColor = 'danger';
            $sentimentIcon = 'ti-mood-sad';
            $sentimentText = 'Negative';
            $sentimentBg = 'bg-red-lt';
        } else {
            $sentimentColor = 'secondary';
            $sentimentIcon = 'ti-mood-empty';
            $sentimentText = 'Neutral';
            $sentimentBg = 'bg-azure-lt';
        }
        $analysis = $callLog->ai_response['analysis'] ?? [];
    @endphp

    <!-- Row 1: Call Info Cards -->
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
                            <div class="font-weight-medium">Timing</div>
                            <div class="text-muted">{{ $callLog->call_timing ? $callLog->call_timing->format('d M Y, H:i') : $callLog->created_at->format('d M Y, H:i') }} &bull; {{ $callLog->call_duration ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Summary (Full Width) -->
    <div class="card mb-3 {{ $sentimentBg }}">
        <div class="card-body">
            <div class="d-flex align-items-start">
                <div class="me-3">
                    <span class="avatar bg-{{ $sentimentColor }} text-white avatar-lg">
                        <i class="ti {{ $sentimentIcon }}" style="font-size: 1.5rem;"></i>
                    </span>
                </div>
                <div>
                    <h3 class="mb-1">Call Summary</h3>
                    @if(!empty($analysis['summary']))
                        <p class="mb-0 text-dark" style="font-size: 1.1rem; line-height: 1.7;">
                            {{ $analysis['summary'] }}
                        </p>
                    @else
                        <p class="mb-0 text-dark" style="font-size: 1.1rem; line-height: 1.7;">
                            {{ is_array($analysis['feedback'] ?? null) ? implode(', ', $analysis['feedback']) : ($analysis['feedback'] ?? 'सारांश उपलब्ध नहीं है।') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Insights (2 columns) -->
    <div class="row row-cards mb-3">
        
        <!-- Left: Key Points + Feedback -->
        <div class="col-lg-6">
            
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-list-check me-2 text-blue"></i>मुख्य बिंदु (Key Points)</h3>
                </div>
                <div class="card-body">
                    @if(!empty($analysis['key_points']))
                        <div class="list-group list-group-flush">
                            @foreach($analysis['key_points'] as $idx => $point)
                                <div class="list-group-item px-0 border-0">
                                    <div class="d-flex">
                                        <span class="badge bg-blue-lt text-blue me-2 mt-1" style="min-width: 24px; height: 24px; line-height: 24px;">{{ $idx + 1 }}</span>
                                        <span style="font-size: 1.05rem;">{{ $point }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted">कोई मुख्य बिंदु नहीं मिला।</span>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-message-2 me-2 text-green"></i>प्रतिक्रिया (Feedback)</h3>
                </div>
                <div class="card-body">
                    @if(!empty($analysis['feedback']))
                        <p class="mb-0 text-dark" style="font-size: 1.05rem; line-height: 1.7;">
                            {{ is_array($analysis['feedback']) ? implode(', ', $analysis['feedback']) : $analysis['feedback'] }}
                        </p>
                    @else
                        <span class="text-muted">कोई प्रतिक्रिया नहीं दी गई।</span>
                    @endif
                </div>
            </div>

        </div>

        <!-- Right: Complaints + Keywords -->
        <div class="col-lg-6">

            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-alert-triangle me-2 text-red"></i>शिकायतें (Complaints)</h3>
                </div>
                <div class="card-body">
                    @if(!empty($analysis['complaints']))
                        <div class="list-group list-group-flush">
                            @foreach($analysis['complaints'] as $complaint)
                                <div class="list-group-item px-0 border-0">
                                    <div class="d-flex align-items-start">
                                        <span class="text-danger me-2 mt-1"><i class="ti ti-alert-circle"></i></span>
                                        <span style="font-size: 1.05rem; color: #d63939;"><strong>{{ $complaint }}</strong></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="ti ti-checks text-success" style="font-size: 2rem;"></i>
                            <div class="text-success mt-2 fw-bold">कोई शिकायत दर्ज नहीं हुई</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-tags me-2 text-yellow"></i>महत्वपूर्ण शब्द (Keywords)</h3>
                </div>
                <div class="card-body">
                    @if(!empty($analysis['keywords']))
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($analysis['keywords'] as $keyword)
                                <span class="badge bg-yellow-lt text-yellow px-3 py-2" style="font-size: 0.95rem;">{{ $keyword }}</span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted">कोई keyword नहीं मिला।</span>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Row 4: Full Transcript (Bottom, Full Width) -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-quote me-2"></i>Full Call Transcript</h3>
        </div>
        <div class="card-body bg-light" style="max-height: 600px; overflow-y: auto;">
            @if($callLog->audio_text)
                <div style="font-size: 1.05rem; line-height: 1.8; color: #333; white-space: pre-wrap;">{{ $callLog->audio_text }}</div>
            @else
                <div class="empty py-4">
                    <div class="empty-icon"><i class="ti ti-microphone-off fs-1 text-muted"></i></div>
                    <p class="empty-title text-muted mt-3">ट्रांसक्रिप्शन उपलब्ध नहीं है</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
