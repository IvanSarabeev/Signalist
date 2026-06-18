<?php

namespace App\Presentation\Http\Exception\Services\Alert;

use App\Presentation\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class AlertDeletionFailed extends HttpException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function getErrorMessage(): string
    {
        return "Failed to delete the alert. Please try again later.";
    }
}
