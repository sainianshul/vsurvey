<?php

namespace App\Http\Requests\Api\Auth;

use App\Models\User;
use App\Rules\IndianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                new IndianPhoneNumber,
            ],
            'otp' => [
                'required',
                'digits:6',
            ],
            'device_id' => [
                'required',
                'string',
                'max:100',
            ],
            'device_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'device_type' => [
                'nullable',
                'integer',
                Rule::in([1, 2, 3]), // 1=ANDROID, 2=IOS, 3=WEB
            ],
            'fcm_token' => [
                'nullable',
                'string',
            ],
            'latitude' => [
                'nullable',
                'numeric',
            ],
            'longitude' => [
                'nullable',
                'numeric',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Phone number is required.',
            'otp.required' => 'OTP is required.',
            'otp.digits' => 'OTP must be 6 digits.',
            'device_id.required' => 'Device ID is required.',
            'device_type.in' => 'Device type must be 1 (Android), 2 (iOS), or 3 (Web).',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'phone' => [
                'description' => 'Valid Indian mobile number.',
                'example' => '9876543210',
            ],
            'otp' => [
                'description' => '6 digit OTP.',
                'example' => '123456',
            ],
            'device_id' => [
                'description' => 'Unique device identifier.',
                'example' => 'abc-123-def',
            ],
            'device_name' => [
                'description' => 'Name of the device.',
                'example' => 'Samsung Galaxy S24',
            ],
            'device_type' => [
                'description' => '1 = ANDROID, 2 = IOS, 3 = WEB',
                'example' => 1,
            ],
            'fcm_token' => [
                'description' => 'Firebase Cloud Messaging token for push notifications.',
                'example' => 'fcm_xxxxxxxxx',
            ],
            'latitude' => [
                'description' => 'Current latitude of the user.',
                'example' => '28.7041',
            ],
            'longitude' => [
                'description' => 'Current longitude of the user.',
                'example' => '77.1025',
            ],
        ];
    }
}
