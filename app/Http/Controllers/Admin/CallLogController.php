<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CallLog;
use App\Models\User;
use App\DataTables\CallLogs\CallLogDataTable;
use Illuminate\Support\Facades\Http;

class CallLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CallLogDataTable $dataTable)
    {
        return $dataTable->render('admin.call_logs.index');
    }

    /**
     * Show the form for creating a new manual call log.
     */
    public function create()
    {
        $agents = User::where('role', User::ROLE_USER)->get();
        return view('admin.call_logs.create', compact('agents'));
    }

    /**
     * Store a manual call log.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'phone_number' => 'required|string|max:15',
            'name' => 'nullable|string|max:255',
            'audio' => 'required|file|mimes:mp3,wav,ogg,m4a,mp4|max:10240', // 10MB max
            'call_timing' => 'required|date',
            'call_duration' => 'nullable|string',
        ]);

        $file = $request->file('audio');

        try {
            $response = Http::attach(
                'file', 
                file_get_contents($file->getRealPath()), 
                $file->getClientOriginalName()
            )->post(env('SPEECH_SERVICE_URL', 'http://localhost:5000') . '/transcribe');

            if ($response->successful()) {
                $result = $response->json();
                
                $analysis = $result['analysis'] ?? [];
                
                CallLog::create([
                    'user_id' => $request->user_id,
                    'phone_number' => $request->phone_number,
                    'name' => $request->name,
                    'call_timing' => $request->call_timing,
                    'call_duration' => $request->call_duration,
                    'audio_text' => $result['text'] ?? null,
                    'sentiment' => $analysis['sentiment'] ?? 'unknown',
                    'ai_response' => $result,
                    'model_used' => $analysis['model_used'] ?? 'unknown',
                    'is_success' => true
                ]);

                return redirect()->route('admin.call-logs.index')->with('success', 'Manual Call Log uploaded and analyzed successfully!');
            }

            return back()->with('error', 'Error from speech service: ' . $response->body());
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to connect to speech service: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified call log.
     */
    public function show(CallLog $callLog)
    {
        $callLog->load('user');
        return view('admin.call_logs.show', compact('callLog'));
    }
}
