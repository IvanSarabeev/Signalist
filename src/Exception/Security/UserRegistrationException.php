<?php

namespace App\Exception\Security;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;

class UserRegistrationException extends DomainException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorMessage(): string
    {
        return 'Something went wrong while registering an account. Please try again later.';
    }
}
