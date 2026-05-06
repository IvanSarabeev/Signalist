<?php

namespace App\Presentation\Http\Exception\Services;

use App\Presentation\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

final class WatchlistItemNotFound extends HttpException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorMessage(): string
    {
        return 'Watchlist item not found';
    }
}
