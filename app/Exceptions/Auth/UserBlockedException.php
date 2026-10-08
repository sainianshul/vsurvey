<?php

namespace App\Exceptions\Auth;

use App\Exceptions\ApiException;

class UserBlockedException extends ApiException
{
    protected int $defaultStatus = 403;
}
