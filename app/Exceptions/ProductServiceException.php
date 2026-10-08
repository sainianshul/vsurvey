<?php

namespace App\Exceptions;

class ProductServiceException extends ApiException
{
    protected int $defaultStatus = 400;
}
