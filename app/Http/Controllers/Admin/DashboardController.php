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
        // Cache the dashboard stats for 5 minutes
        $data = Cache::remember('admin.dashboard.stats', 300, function () {
            
            // Top Level Stats
            $totalUsers = User::count();
            
            // Recent Users for the table
            $recentUsers = User::latest()
                ->take(5)
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'initials' => strtoupper(substr($user->name ?? 'US', 0, 2)),
                        'phone' => $user->phone,
                        'joined' => $user->created_at->format('d M Y'),
                        'status' => $user->status,
                        'status_name' => $user->status_name,
                        'status_color' => $user->status === User::STATUS_ACTIVE ? 'success' : ($user->status === User::STATUS_PENDING ? 'warning' : 'danger')
                    ];
                });

            // Chart Data: Users added in the last 30 days
            $chartDates = [];
            $chartCounts = [];
            
            $startDate = Carbon::now()->subDays(29)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            
            // Get counts grouped by date
            $userCounts = User::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date');
                
            // Fill missing dates with 0
            for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
                $dateStr = $date->format('Y-m-d');
                $chartDates[] = $date->format('d M');
                $chartCounts[] = $userCounts[$dateStr] ?? 0;
            }

            return [
                'total_users' => number_format($totalUsers),
                'recent_users' => $recentUsers,
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
