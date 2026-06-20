<?php

declare(strict_types=1);

namespace App\Presentation\Http\Exception\Services\Watchlist;

use App\Presentation\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class WatchlistItemDeletionException extends HttpException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function getErrorMessage(): string
    {
        return "An error occurred while deleting the watchlist item. Please try again later.";
    }
}
