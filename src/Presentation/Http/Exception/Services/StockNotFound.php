<?php

namespace App\Presentation\Http\Exception\Services;

use App\Presentation\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

final class StockNotFound extends HttpException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorMessage(): string
    {
        return "Stock not found";
    }

}
