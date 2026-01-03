<?php

namespace App\Exception\Security;

use RuntimeException;
use Throwable;

final class InvalidCredentialsException extends RuntimeException
{
    public function __construct(string $message = 'Invalid credentials.', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
