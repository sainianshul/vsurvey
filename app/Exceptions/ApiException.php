<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Exception;

class ApiException extends Exception
{
    protected int $defaultStatus = 400;

    public function __construct(string $message = '', int $code = 0)
    {
        parent::__construct($message, $code ?: $this->defaultStatus);
    }

    public function render($request)
    {
        // API request → JSON response
        if ($request->expectsJson() || $request->is('api/*')) {
            return ApiResponse::error(
                $this->getMessage(),
                $this->getCode() ?: $this->defaultStatus
            );
        }

        // Web request (Admin Panel) → redirect back with error
        return redirect()->back()->withInput()->with('error', $this->getMessage());
    }
}
