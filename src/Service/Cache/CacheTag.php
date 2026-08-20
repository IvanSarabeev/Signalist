<?php

declare(strict_types=1);

namespace App\Service\Cache;

use App\Entity\Alert;
use App\Entity\Stock;
use App\Entity\User;

final readonly class CacheTag
{
    public static function user(User|int $user): string
    {
        $id = $user instanceof User ? $user->getId() : $user;

        return 'user-' . $id;
    }

    public static function watchlist(User|int $user): string
    {
        return self::user($user) . '-watchlist';
    }

    public static function alerts(User|int $user): string
    {
        return self::user($user) . '-alerts';
    }

    public static function alert(Alert|int $alert): string
    {
        $id = $alert instanceof Alert ? (int) $alert->getId() : $alert;

        return 'alert-' . $id;
    }

    public static function stock(Stock|string $stock): string
    {
        $symbol = $stock instanceof Stock ? $stock->getSymbol() : $stock;

        return 'stock-' . strtoupper($symbol);
    }

    /**
     * Used for Everything related to the Finnhub API
     * @return string
     */
    public static function finnhub(): string
    {
        return 'finnhub';
    }
}
