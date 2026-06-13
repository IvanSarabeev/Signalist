<?php

declare(strict_types=1);

namespace App\Service\Watchlist;

use App\Entity\User;
use App\Entity\WatchlistItem;
use App\Presentation\Http\Request\PaginatedRequest;
use App\Presentation\Http\Response\PaginatedResponse;

interface WatchlistInterface
{
    public function getItems(User $user, PaginatedRequest $pagination): ?PaginatedResponse;

    public function addItem(User $user, string $symbol): WatchlistItem;

    public function deleteItem(User $user, string $symbol): void;
}
