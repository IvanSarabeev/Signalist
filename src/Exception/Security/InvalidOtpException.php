<?php

namespace App\Exception\Security;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;

final class InvalidOtpException extends DomainException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorMessage(): string
    {
        return 'Invalid or expired verification code.';
    }
}
