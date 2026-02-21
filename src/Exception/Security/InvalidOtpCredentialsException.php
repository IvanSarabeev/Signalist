<?php

namespace App\Exception\Security;

use InvalidArgumentException;
use Throwable;

final class InvalidOtpCredentialsException extends InvalidArgumentException
{
    public function __construct(string $message = "Invalid otp code.", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
