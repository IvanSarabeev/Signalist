<?php

declare(strict_types=1);

namespace App\Service\Cache;

enum CacheProfile: string
{
    // Finnhub-backend (external, rate-limited, vbolatile)
    case QUOTE                 = 'finnhub.quote';
    case COMPANY_PROFILE       = 'finnhub.company.profile';
    case COMPANY_NEWS          = 'finnhub.company.news';
    case MARKET_NEWS           = 'finnhub.news';
    case EARNINGS_CALENDAR     = 'finnhub.earnings.calendar';
    case RECOMMENDATION_TRENDS = 'finnhub.recommendation.trends';

    // Aggregates (expensive fan-out over several Finnhub calls)
    case MARKET_INDICES        = 'market.indices';
    case MARKET_TRENDING       = 'market.trending';

    // Database-backend
    case USER_WATCHLIST        = 'user.watchlist';
    case USER_WATCHLIST_COUNT  = 'user.watchlist.count';
    case USER_ALERTS           = 'user.alerts';
    case ALERT_DETAIL          = 'alert.detail';

    public function ttl(): int
    {
        return match ($this) {
            self::QUOTE                 => 60,
            self::MARKET_NEWS,
            self::COMPANY_NEWS          => 300,
            self::MARKET_INDICES        => 45,
            self::MARKET_TRENDING       => 600,
            self::EARNINGS_CALENDAR     => 1_800,
            self::RECOMMENDATION_TRENDS => 3_600,
            self::COMPANY_PROFILE       => 86_400,

            self::USER_WATCHLIST,
            self::USER_WATCHLIST_COUNT,
            self::USER_ALERTS           => 900,
        };
    }

    /**
     * Fraction of the base TTL added as uniform random spread.
     *
     *  0.25 on a 60s TTL means the real lifetime lands anywhere in [60s, 75s].
     *  The spread is additive only — a key never lives *less* than its base TTL,
     *  so freshness guarantees are never weakened.
     */
    public function cacheJitter(): float
    {
        return match ($this) {
            self::QUOTE                 => 0.35,
            self::COMPANY_PROFILE       => 0.20,
            self::MARKET_INDICES,
            self::MARKET_TRENDING       => 0.30,
            self::COMPANY_NEWS,
            self::MARKET_NEWS           => 0.25,
            default                     => 0.15,
        };
    }

    /**
     * Probablistivc early-expiration factor for stampede protection.
     */
    public function secureStampede(): float
    {
        return match ($this) {
            self::QUOTE,
            self::MARKET_INDICES,
            self::MARKET_TRENDING   => 2.0,
            self::COMPANY_PROFILE,
            self::EARNINGS_CALENDAR => 1.5,
            default                 => 1.0
        };
    }

    public function emptyTtl(): int
    {
        return min(120, $this->ttl());
    }
}
