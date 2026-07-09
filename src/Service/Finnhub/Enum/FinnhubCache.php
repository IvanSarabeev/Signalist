<?php

declare(strict_types=1);

namespace App\Service\Finnhub\Enum;

enum FinnhubCache: string
{
    case NEWS            = 'finnhub.news';
    case COMPANY_NEWS    = 'finnhub.company.news';
    case COMPANY_PROFILE = 'finnhub.company.profile';
    case QUOTE           = 'finnhub.quote';

    public function ttl(): int
    {
        return match ($this) {
            // 5 minutes due to time sensitivity
            self::NEWS, self::COMPANY_NEWS => 300,
            // 24 hours
            self::COMPANY_PROFILE          => 3600,
            // 1 minute cache due to dynamic prices
            self::QUOTE                    => 60,
        };
    }

    public function key(string $suffix): string
    {
        return $this->value . $suffix;
    }
}
