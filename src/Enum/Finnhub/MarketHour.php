<?php

declare(strict_types=1);

namespace App\Enum\Finnhub;

enum MarketHour: string
{
    case AFTER_MARKET  = 'amc';
    case DURING_MARKET = 'dmh';
    case BEFORE_MARKET = 'bmo';

    public static function earningsHour(?string $earningType): ?string
    {
        return match ($earningType) {
            self::AFTER_MARKET->value  => 'After Market',
            self::DURING_MARKET->value => 'During Market',
            self::BEFORE_MARKET->value => 'Before Market',
            default                    => null,
        };
    }
}
