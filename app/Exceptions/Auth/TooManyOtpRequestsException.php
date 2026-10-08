<?php

namespace App\Exceptions\Auth;

use App\Exceptions\ApiException;

class TooManyOtpRequestsException extends ApiException
{
    protected int $defaultStatus = 429;
}
