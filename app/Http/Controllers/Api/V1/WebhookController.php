<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\CallLog;
use App\Models\User;

class WebhookController extends Controller
{
    /**
     * Handle incoming webhooks from Telecmi / Twilio
     */
    public function handleIncomingCall(Request $request)
    {
        Log::info('Incoming Call Webhook Received', $request->all());

        // Extract the recording URL
        $recordingUrl = $request->input('RecordingUrl') 
                        ?? $request->input('recording_url') 
                        ?? $request->input('file_url');

        $providerCallId = $request->input('CallSid') // Twilio
                  ?? $request->input('uuid'); // Telecmi
                  
        $fromNumber = $request->input('From') ?? $request->input('from'); // Agent
        $toNumber = $request->input('To') ?? $request->input('to'); // Voter
        $duration = $request->input('CallDuration') ?? $request->input('duration');

        // Prevent Duplicate Processing
        if ($providerCallId && CallLog::where('provider_call_id', $providerCallId)->exists()) {
            Log::info('Webhook ignored: Call already processed.', ['call_id' => $providerCallId]);
            return response()->json(['status' => 'ignored', 'message' => 'Call already processed']);
        }

        if (!$recordingUrl) {
            Log::warning('No recording URL found in webhook payload', $request->all());
            return response()->json(['status' => 'ignored', 'message' => 'No recording URL provided']);
        }

        // Try to link to an agent based on From number
        $agent = User::where('phone', 'like', '%' . ltrim($fromNumber, '+') . '%')->first();

        try {
            // 1. Download the audio file from the provider
            $audioContent = Http::get($recordingUrl)->body();
            
            if (!$audioContent) {
                throw new \Exception("Failed to download audio from $recordingUrl");
            }

            // Create a temporary file to send to our Python Microservice
            $tempFilePath = sys_get_temp_dir() . '/' . ($providerCallId ?? uniqid('call_')) . '.mp3';
            file_put_contents($tempFilePath, $audioContent);

            // 2. Send the audio to our Python Speech-to-Text Microservice
            $speechServiceUrl = env('SPEECH_SERVICE_URL', 'http://speech:5000') . '/transcribe';
            
            $response = Http::attach(
                'file', 
                file_get_contents($tempFilePath), 
                ($providerCallId ?? 'webhook') . '.mp3'
            )->post($speechServiceUrl);

            // Cleanup temp file
            @unlink($tempFilePath);

            // 3. Process and Log the result
            if ($response->successful()) {
                $result = $response->json();
                
                $analysis = $result['analysis'] ?? [];
                
                CallLog::create([
                    'provider_call_id' => $providerCallId,
                    'user_id' => $agent ? $agent->id : null,
                    'phone_number' => $toNumber ?? 'Unknown',
                    'name' => null,
                    'call_timing' => now(),
                    'call_duration' => $duration,
                    'audio_text' => $result['text'] ?? null,
                    'sentiment' => $analysis['sentiment'] ?? 'unknown',
                    'ai_response' => $result,
                    'model_used' => $analysis['model_used'] ?? 'unknown',
                    'is_success' => true
                ]);
                
                Log::info('Successfully processed webhook call: ' . $providerCallId);
                return response()->json(['status' => 'success', 'data' => $result]);
            }

            // Log Error from microservice
            CallLog::create([
                'provider_call_id' => $providerCallId,
                'phone_number' => $toNumber ?? 'Unknown',
                'call_timing' => now(),
                'model_used' => 'none',
                'is_success' => false,
                'ai_response' => ['error_message' => 'Speech service error: ' . $response->body()]
            ]);

            return response()->json(['status' => 'error', 'message' => 'Speech service failed'], 500);

        } catch (\Exception $e) {
            Log::error('Webhook Processing Error: ' . $e->getMessage());
            
            CallLog::create([
                'provider_call_id' => $providerCallId,
                'phone_number' => $toNumber ?? 'Unknown',
                'call_timing' => now(),
                'model_used' => 'none',
                'is_success' => false,
                'ai_response' => ['error_message' => 'Webhook Exception: ' . $e->getMessage()]
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
