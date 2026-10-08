<?php

namespace App\Services;

use App\Exceptions\Auth\InvalidOtpException;
use App\Exceptions\Auth\TooManyOtpRequestsException;
use App\Exceptions\Auth\UserBlockedException;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthService
{
    private const OTP_COOLDOWN_SECONDS = 60;
    private const OTP_EXPIRY_MINUTES = 10;
    private const OTP_MAX_ATTEMPTS = 3;

    public function sendOtp(string $phone)
    {
        $cooldownTime = now()->subSeconds(self::OTP_COOLDOWN_SECONDS);

        $recentOtpExists = OtpVerification::where('phone', $phone)
            ->where('created_at', '>=', $cooldownTime)
            ->exists();

        if ($recentOtpExists) {
            throw new TooManyOtpRequestsException(
                'Please wait before requesting another OTP.'
            );
        }

        OtpVerification::clearPhoneOtps($phone);

        $otp = (string) random_int(100000, 999999);
        
        // PlayStore Review / Apple App Store bypass
        if ($phone === '9999999999') {
            $otp = '123456';
        }

        $expiryTime = now()->addMinutes(self::OTP_EXPIRY_MINUTES);

        // store otp in database (plain text)
        OtpVerification::create([
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => $expiryTime,
            'status' => OtpVerification::STATUS_ACTIVE,
        ]);

        $messageText = "Your OTP for Login is {$otp}, Valid for 10 minutes. Do not share it with anyone. Thanks CAN WINN FOUNDATION";
        $messageEncoded = str_replace(' ', '%20', $messageText);

        $smartpingUser = config('services.smartping.user');
        $smartpingPass = config('services.smartping.pass');
        $smartpingFrom = config('services.smartping.from');

        // Uncomment in production to enable actual SMS sending
        if ($phone !== '9999999999' && $smartpingUser && $smartpingPass) {
            $url = "https://api.smartping.ai/fe/api/v1/send?"
                . "username={$smartpingUser}"
                . "&password={$smartpingPass}"
                . "&unicode=false"
                . "&from={$smartpingFrom}"
                . "&to={$phone}"
                . "&text={$messageEncoded}";

            try {
                $response = \Illuminate\Support\Facades\Http::get($url);
                if ($response->successful()) {
                    \Illuminate\Support\Facades\Log::info('SmartPing OTP sent successfully', ['phone' => $phone, 'response' => $response->body()]);
                    \App\Models\CommunicationLog::log($phone, $messageText, \App\Models\CommunicationLog::STATUS_SENT);
                } else {
                    \Illuminate\Support\Facades\Log::error('SmartPing OTP failed', ['phone' => $phone, 'response' => $response->body()]);
                    \App\Models\CommunicationLog::log($phone, $messageText, \App\Models\CommunicationLog::STATUS_FAILED);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('SmartPing API Exception', ['error' => $e->getMessage()]);
                \App\Models\CommunicationLog::log($phone, $messageText, \App\Models\CommunicationLog::STATUS_FAILED);
            }
        } else {
            // Log pending if credentials missing
            \App\Models\CommunicationLog::log($phone, $messageText, \App\Models\CommunicationLog::STATUS_PENDING);
        }
        
        return [
            'otp' => $otp,
        ];
    }

    public function verifyOtp(array $data, string $ip, string $userAgent)
    {
        $otpRecord = OtpVerification::getValidOtp($data['phone']);

        if (!$otpRecord) {
            throw new InvalidOtpException('OTP expired or invalid.');
        }

        if ($otpRecord->attempts >= self::OTP_MAX_ATTEMPTS) {
            $otpRecord->deactivate();
            throw new InvalidOtpException('Too many invalid attempts.');
        }

        $isValidOtp = ($data['otp'] === $otpRecord->otp);

        if (!$isValidOtp) {
            $otpRecord->incrementOtpAttempts();
            throw new InvalidOtpException('Invalid OTP.');
        }

        $otpRecord->markAsUsed();

        $user = User::where('phone', $data['phone'])->first();
        if (!$user) {
            $user = DB::transaction(function () use ($data) {
                return User::create([
                    'phone' => $data['phone'],
                    'role' => User::ROLE_USER, // default role for new registrations
                    'status' => User::STATUS_ACTIVE,
                    'phone_verified_at' => now(),
                    'created_by' => 0, // 0 for self registered
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'profile_completed_at' => null,
                ]);
            });
        }

        if ($user->status === User::STATUS_BLOCKED) {
            throw new UserBlockedException(
                $user->blocked_reason ?? 'Your account has been blocked.'
            );
        }

        $user->update([
            'last_login_at' => now(),
            'phone_verified_at' => now(),
            'location_updated_at' => (isset($data['latitude']) && isset($data['longitude'])) ? now() : $user->location_updated_at,
            'latitude' => $data['latitude'] ?? $user->latitude,
            'longitude' => $data['longitude'] ?? $user->longitude,
        ]);

        $token = $this->generateDeviceToken($user, $data, $ip, $userAgent);

        return [
            'token' => $token,
            'user' => $user,
            'is_profile_complete' => !is_null($user->profile_completed_at),
        ];
    }

    public function logout(User $user)
    {
        // Delete ONLY current device token
        $user->currentAccessToken()?->delete();
    }

    public function logoutAllDevices(User $user)
    {
        // Delete ALL tokens
        $user->tokens()->delete();
    }

    private function generateDeviceToken(User $user, array $data, string $ip, string $userAgent)
    {
        // Create token
        $tokenResult = $user->createToken('auth-token');
        
        // Update the token with device info
        $accessToken = $tokenResult->accessToken;
        $accessToken->device_id = $data['device_id'] ?? null;
        $accessToken->device_name = $data['device_name'] ?? null;
        $accessToken->device_type = $data['device_type'] ?? null;
        $accessToken->fcm_token = $data['fcm_token'] ?? null;
        $accessToken->latitude = $data['latitude'] ?? null;
        $accessToken->longitude = $data['longitude'] ?? null;
        $accessToken->ip_address = $ip;
        $accessToken->user_agent = $userAgent;
        $accessToken->save();

        return $tokenResult->plainTextToken;
    }
}
