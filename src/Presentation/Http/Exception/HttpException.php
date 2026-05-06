<?php

namespace App\Presentation\Http\Exception;

use RuntimeException;

abstract class HttpException extends RuntimeException implements HttpExceptionInterface
{
    abstract public function getStatusCode(): int;

    abstract public function getErrorMessage(): string;

    public function getErrorCode(): string
    {
        return 'DOMAIN_ERROR';
    }
}
