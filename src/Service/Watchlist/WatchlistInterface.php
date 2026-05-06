<?php

declare(strict_types=1);

namespace App\Service\Watchlist;

use App\Entity\User;
use App\Entity\WatchlistItem;

interface WatchlistInterface
{
    public function getItems(User $user): ?array;

    public function addItem(User $user, string $symbol): WatchlistItem;

    public function deleteItem(string $symbol): void;
}
