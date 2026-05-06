<?php

namespace App\Presentation\Http\Exception\Services;

use App\Presentation\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

final class StockExistingInWatchlistException extends HttpException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }

    public function getErrorMessage(): string
    {
        return 'Stock already in watchlist';
    }

}
