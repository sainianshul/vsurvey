<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Profile\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    #[OA\Post(
        path: '/api/v1/profile',
        operationId: 'updateProfile',
        summary: 'Update user profile (Name, Address, etc.)',
        security: [['bearerAuth' => []]],
        tags: ['Profile'],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        required: ['name'],
                        properties: [
                            new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                            new OA\Property(property: 'email', type: 'string', nullable: true, example: 'john@example.com'),
                            new OA\Property(property: 'address', type: 'string', example: '123, Main Street, Near Park'),
                            new OA\Property(property: 'state', type: 'string', example: 'Delhi'),
                            new OA\Property(property: 'city', type: 'string', example: 'New Delhi'),
                            new OA\Property(property: 'pincode', type: 'string', example: '110001'),
                            new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 28.7041),
                            new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 77.1025),
                            new OA\Property(property: 'profile_photo', type: 'string', format: 'binary', nullable: true),
                        ]
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('users/photos', 'public');
        }

        // Set profile_completed_at when they successfully update profile
        if (is_null($user->profile_completed_at)) {
            $data['profile_completed_at'] = now();
        }

        if (isset($data['latitude']) && isset($data['longitude'])) {
            $data['location_updated_at'] = now();
        }

        $user->update($data);

        return ApiResponse::success(
            'Profile updated successfully',
            [
                'user' => $user->fresh()->toApiResponse(),
            ]
        );
    }
}
