<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\AILog;

class WebhookController extends Controller
{
    /**
     * Handle incoming webhooks from Telecmi / Twilio
     */
    public function handleIncomingCall(Request $request)
    {
        Log::info('Incoming Call Webhook Received', $request->all());

        // Extract the recording URL based on the provider (Twilio or Telecmi)
        // Twilio sends 'RecordingUrl'
        // Telecmi typically sends 'recording_url' or 'file'
        $recordingUrl = $request->input('RecordingUrl') 
                        ?? $request->input('recording_url') 
                        ?? $request->input('file_url');

        $callId = $request->input('CallSid') // Twilio
                  ?? $request->input('uuid') // Telecmi
                  ?? uniqid('call_');

        if (!$recordingUrl) {
            Log::warning('No recording URL found in webhook payload', $request->all());
            return response()->json(['status' => 'ignored', 'message' => 'No recording URL provided']);
        }

        try {
            // 1. Download the audio file from the provider
            $audioContent = Http::get($recordingUrl)->body();
            
            if (!$audioContent) {
                throw new \Exception("Failed to download audio from $recordingUrl");
            }

            // Create a temporary file to send to our Python Microservice
            $tempFilePath = sys_get_temp_dir() . '/' . $callId . '.mp3';
            file_put_contents($tempFilePath, $audioContent);

            // 2. Send the audio to our Python Speech-to-Text Microservice
            $speechServiceUrl = env('SPEECH_SERVICE_URL', 'http://speech:5000') . '/transcribe';
            
            $response = Http::attach(
                'file', 
                file_get_contents($tempFilePath), 
                $callId . '.mp3'
            )->post($speechServiceUrl);

            // Cleanup temp file
            @unlink($tempFilePath);

            // 3. Process and Log the result
            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['analysis'])) {
                    AILog::create([
                        'filename' => $callId,
                        'model_used' => $result['analysis']['model_used'] ?? 'unknown',
                        'sentiment' => $result['analysis']['sentiment'] ?? 'unknown',
                        'is_success' => true,
                        'full_response' => $result
                    ]);
                }
                
                Log::info('Successfully processed webhook call: ' . $callId);
                return response()->json(['status' => 'success', 'data' => $result]);
            }

            // Log Error from microservice
            AILog::create([
                'filename' => $callId,
                'model_used' => 'none',
                'is_success' => false,
                'error_message' => 'Speech service error: ' . $response->body()
            ]);

            return response()->json(['status' => 'error', 'message' => 'Speech service failed'], 500);

        } catch (\Exception $e) {
            Log::error('Webhook Processing Error: ' . $e->getMessage());
            
            AILog::create([
                'filename' => $callId,
                'model_used' => 'none',
                'is_success' => false,
                'error_message' => 'Webhook Exception: ' . $e->getMessage()
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
