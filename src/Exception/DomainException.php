<?php

namespace App\Exception;

use RuntimeException;

abstract class DomainException extends RuntimeException implements DomainExceptionInterface
{
    abstract public function getStatusCode(): int;

    abstract public function getErrorMessage(): string;

    public function getErrorCode(): string
    {
        return 'DOMAIN_ERROR';
    }
}
