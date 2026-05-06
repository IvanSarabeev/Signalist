<?php

namespace App\Presentation\Http\Exception\Security;

use App\Presentation\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class UserRegistrationException extends HttpException
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
