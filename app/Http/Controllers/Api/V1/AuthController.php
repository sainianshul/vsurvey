<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\SendOtpRequest;
use App\Http\Requests\Api\Auth\VerifyOtpRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    #[OA\Post(
        path: '/api/v1/auth/send-otp',
        operationId: 'sendOtp',
        summary: 'Send OTP to mobile number',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['phone'],
                properties: [
                    new OA\Property(property: 'phone', type: 'string', example: '9876543210', description: '10-digit Indian mobile number'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 422, description: 'Validation Error'),
            new OA\Response(response: 429, description: 'Too Many Requests'),
        ]
    )]
    public function sendOtp(SendOtpRequest $request)
    {
        $result = $this->authService->sendOtp(
            $request->string('phone')->value()
        );

        $message = 'OTP sent successfully';

        // Only show OTP in response if we are not in production or explicit config allows it
        if (!app()->environment('production')) {
            $message .= " ({$result['otp']})";
        }

        return ApiResponse::success(
            $message
        );
    }

    #[OA\Post(
        path: '/api/v1/auth/verify-otp',
        operationId: 'verifyOtp',
        summary: 'Verify OTP and authenticate user',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['phone', 'otp', 'device_id'],
                properties: [
                    new OA\Property(property: 'phone', type: 'string', example: '9876543210'),
                    new OA\Property(property: 'otp', type: 'string', example: '123456'),
                    new OA\Property(property: 'device_id', type: 'string', example: 'abc-123-def'),
                    new OA\Property(property: 'device_name', type: 'string', nullable: true, example: 'Samsung Galaxy S24'),
                    new OA\Property(property: 'device_type', type: 'integer', nullable: true, example: 1, description: '1=ANDROID, 2=IOS, 3=WEB'),
                    new OA\Property(property: 'fcm_token', type: 'string', nullable: true, example: 'fcm_xxxxxxxxx'),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 28.7041),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 77.1025),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 422, description: 'Validation Error or Invalid OTP'),
            new OA\Response(response: 403, description: 'User Blocked'),
        ]
    )]
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $result = $this->authService->verifyOtp(
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return ApiResponse::success(
            'Authentication successful',
            [
                'token' => $result['token'],
                'is_profile_complete' => $result['is_profile_complete'],
                'user' => $result['user']->toApiResponse(),
            ]
        );
    }

    #[OA\Post(
        path: '/api/v1/auth/logout',
        operationId: 'logout',
        summary: 'Logout current device',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(
            'Logged out successfully'
        );
    }

    #[OA\Post(
        path: '/api/v1/auth/logout-all',
        operationId: 'logoutAll',
        summary: 'Logout all devices',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function logoutAll(Request $request)
    {
        $this->authService->logoutAllDevices($request->user());

        return ApiResponse::success(
            'Logged out from all devices successfully'
        );
    }

    #[OA\Get(
        path: '/api/v1/auth/me',
        operationId: 'me',
        summary: 'Get authenticated user profile',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function me(Request $request)
    {
        return ApiResponse::success(
            'Profile fetched successfully',
            [
                'user' => $request->user()->toApiResponse(),
            ]
        );
    }
}
