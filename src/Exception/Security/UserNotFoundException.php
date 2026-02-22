<?php

namespace App\Exception\Security;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;

class UserNotFoundException extends DomainException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorMessage(): string
    {
        return "Invalid user.";
    }
}
