<?php

namespace App\Exception\Security;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;

final class InvalidCredentialsException extends DomainException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorMessage(): string
    {
        return 'Invalid credentials.';
    }
}
