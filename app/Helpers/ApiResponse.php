<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success(
        string $message = 'Success',
        array $data = [],
        int $status = 200
    ) {

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now(),
        ], $status);
    }

    public static function error(
        string $message = 'Something went wrong',
        int $status = 400,
        array $errors = []
    ) {

        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now(),
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json(
            $response,
            $status
        );
    }
}
