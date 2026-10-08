<?php
return [
    'expiry_minutes' => env('OTP_EXPIRY_MINUTES', 10),
    'max_attempts' => env('OTP_MAX_ATTEMPTS', 3),
    'cooldown_seconds' => env('OTP_COOLDOWN_SECONDS', 60),
];