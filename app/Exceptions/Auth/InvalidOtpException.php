<?php

namespace App\Exceptions\Auth;

use App\Exceptions\ApiException;

class InvalidOtpException extends ApiException
{
    protected int $defaultStatus = 422;
}
