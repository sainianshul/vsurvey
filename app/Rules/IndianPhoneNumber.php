<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IndianPhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isValidPhone = preg_match('/^[6-9]\d{9}$/', $value);

        if (!$isValidPhone) {
            $fail('Please enter a valid 10-digit Indian mobile number.');
        }
    }
}
