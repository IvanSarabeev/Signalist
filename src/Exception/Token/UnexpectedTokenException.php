<?php

namespace App\Exception\Token;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;

class UnexpectedTokenException extends DomainException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    public function getErrorMessage(): string
    {
        return 'Token not found or expired';
    }
}
