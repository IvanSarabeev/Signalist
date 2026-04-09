<?php

namespace App\Exception\Common;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;

class InvalidPaginationArgumentException extends DomainException
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
