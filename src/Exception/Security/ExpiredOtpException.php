<?php

namespace App\Exception\Security;

use LogicException;
use Throwable;

final class ExpiredOtpException extends LogicException
{
    public function __construct(string $message = "Invalid or expired OTP", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
