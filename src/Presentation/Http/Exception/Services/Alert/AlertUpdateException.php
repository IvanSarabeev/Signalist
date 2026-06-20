<?php

namespace App\Presentation\Http\Exception\Services\Alert;

use App\Presentation\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class AlertUpdateException extends HttpException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorMessage(): string
    {
        return "Unable to update alert";
    }
}
