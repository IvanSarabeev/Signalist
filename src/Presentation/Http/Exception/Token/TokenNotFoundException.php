<?php

namespace App\Presentation\Http\Exception\Token;

use App\Presentation\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

final class TokenNotFoundException extends HttpException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    public function getErrorMessage(): string
    {
        return "Invalid token.";
    }
}
