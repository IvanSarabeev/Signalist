<?php

namespace App\Presentation\Http\Exception\Security;

use App\Presentation\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class UserNotFoundException extends HttpException
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
