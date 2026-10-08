<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AILog;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function stats()
    {
        $data = Cache::remember('admin.dashboard.stats', 60, function () {
            
            // Top Level Stats
            $totalCalls = \App\Models\CallLog::count();
            $todayCalls = \App\Models\CallLog::whereDate('created_at', Carbon::today())->count();
            $positiveCalls = \App\Models\CallLog::where('sentiment', 'positive')->count();
            $negativeCalls = \App\Models\CallLog::where('sentiment', 'negative')->count();
            
            // Recent Calls for the table
            $recentCalls = \App\Models\CallLog::with('user')->latest()->take(5)->get()->map(function($call) {
                return [
                    'id' => $call->id,
                    'phone' => $call->phone_number,
                    'agent' => $call->user ? $call->user->name : 'Webhook',
                    'sentiment' => ucfirst($call->sentiment ?? 'Neutral'),
                    'sentiment_color' => strtolower($call->sentiment) == 'positive' ? 'success' : (strtolower($call->sentiment) == 'negative' ? 'danger' : 'secondary'),
                    'timing' => $call->call_timing ? $call->call_timing->format('d M Y, h:i A') : $call->created_at->format('d M Y, h:i A')
                ];
            });

            // Chart Data: Calls over the last 30 days
            $chartDates = [];
            $chartCounts = [];
            
            $startDate = Carbon::now()->subDays(29)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            
            $callCounts = \App\Models\CallLog::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date');
                
            for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
                $dateStr = $date->format('Y-m-d');
                $chartDates[] = $date->format('d M');
                $chartCounts[] = $callCounts[$dateStr] ?? 0;
            }

            return [
                'total_calls' => number_format($totalCalls),
                'today_calls' => number_format($todayCalls),
                'positive_calls' => number_format($positiveCalls),
                'negative_calls' => number_format($negativeCalls),
                'recent_calls' => $recentCalls,
                'chart' => [
                    'dates' => $chartDates,
                    'counts' => $chartCounts
                ]
            ];
        });

        return response()->json($data);
    }

    public function voiceToText()
    {
        return view('admin.voice_to_text');
    }

    public function processVoiceToText(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,ogg,m4a,mp4|max:10240', // 10MB max
        ]);

        $file = $request->file('audio');

        try {
            $response = Http::attach(
                'file', 
                file_get_contents($file->getRealPath()), 
                $file->getClientOriginalName()
            )->post(env('SPEECH_SERVICE_URL', 'http://speech:5000') . '/transcribe');

            if ($response->successful()) {
                $result = $response->json();
                
                // Track in AI Logs Table
                if (isset($result['analysis'])) {
                    AILog::create([
                        'filename' => $file->getClientOriginalName(),
                        'model_used' => $result['analysis']['model_used'] ?? 'unknown',
                        'sentiment' => $result['analysis']['sentiment'] ?? 'unknown',
                        'is_success' => true,
                        'full_response' => $result
                    ]);
                }
                
                return back()->with('json_result', $result);
            }

            // Log Error
            AILog::create([
                'filename' => $file->getClientOriginalName(),
                'model_used' => 'none',
                'is_success' => false,
                'error_message' => 'Error from speech service: ' . $response->body()
            ]);

            return back()->with('error', 'Error from speech service: ' . $response->body());
        } catch (\Exception $e) {
            
            AILog::create([
                'filename' => $file->getClientOriginalName(),
                'model_used' => 'none',
                'is_success' => false,
                'error_message' => 'Failed to connect: ' . $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to connect to speech service: ' . $e->getMessage());
        }
    }
    public function aiLogs()
    {
        $logs = AILog::latest()->paginate(20);
        return view('admin.ai_logs', compact('logs'));
    }
}
