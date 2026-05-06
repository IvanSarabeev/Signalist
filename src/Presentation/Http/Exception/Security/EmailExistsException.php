<?php

namespace App\Presentation\Http\Exception\Security;

use App\Presentation\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

final class EmailExistsException extends HttpException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }

    public function getErrorMessage(): string
    {
        return 'Invalid email address';
    }
}
