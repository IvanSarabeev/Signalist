<?php

declare(strict_types=1);

namespace App\Enum\Alert;

enum AlertFrequency: string
{
    case ONCE           = 'once';
    case ONCE_PER_HOUR  = 'once_per_hour';
    case ONCE_PER_DAY   = 'once_per_day';
    case ONCE_PER_WEEK  = 'once_per_week';
    case ONCE_PER_MONTH = 'once_per_month';
    case MARKET_OPEN    = 'market_open';
    case MARKET_CLOSE   = 'market_close';

    /**
     * Used by Assert\Choice in requests.
     * PHP attributes only accept compile-time constants.
     */
    public const VALUES = [
        'once', 'once_per_hour', 'once_per_day',
        'once_per_week', 'market_open', 'market_close',
    ];

    public function label(): string
    {
        return match($this) {
            self::ONCE           => 'Once (then disable)',
            self::ONCE_PER_HOUR  => 'Once per hour',
            self::ONCE_PER_DAY   => 'Once per day',
            self::ONCE_PER_WEEK  => 'Once per week',
            self::ONCE_PER_MONTH => 'Once per month',
            self::MARKET_OPEN    => 'At market open',
            self::MARKET_CLOSE   => 'At market close',
        };
    }

    /**
     * Minimum seconds that must pass since lastTriggeredAt before firing again.
     * Returns null when there is no cooldown (once/every_time are handled separately).
     */
    public function cooldownSeconds(): ?int
    {
        return match($this) {
            self::ONCE,
            self::ONCE_PER_HOUR => 3_600,
            self::ONCE_PER_DAY  => 86_400,
            self::ONCE_PER_WEEK => 604_800,
            self::ONCE_PER_MONTH => 126_800,
            // once per trading day
            self::MARKET_OPEN,
            self::MARKET_CLOSE  => 86_400,
        };
    }

    public function deactivatesAfterTrigger(): bool
    {
        return $this === self::ONCE;
    }
}
