<?php

declare(strict_types=1);

namespace App\Presentation\Http\Exception\Services\Alert;

use App\Presentation\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class AlertNothingToUpdateException extends HttpException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    public function getErrorMessage(): string
    {
        return 'No fields provided to update.';
    }
}
