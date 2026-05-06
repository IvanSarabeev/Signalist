<?php

namespace App\Presentation\Http\Exception\Common;

use App\Presentation\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class InvalidPaginationArgumentException extends HttpException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorMessage(): string
    {
        return 'Invalid pagination arguments.';
    }
}
