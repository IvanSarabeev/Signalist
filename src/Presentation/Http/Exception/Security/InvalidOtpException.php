<?php

namespace App\Presentation\Http\Exception\Security;

use App\Presentation\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

final class InvalidOtpException extends HttpException
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
