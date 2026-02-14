<?php

namespace App\Exception\Security;

use RuntimeException;
use Throwable;

final class EmailExistsException extends RuntimeException
{
    public function __construct(string $message = "EmailService already in use.", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
