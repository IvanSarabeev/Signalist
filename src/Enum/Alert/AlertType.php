<?php

declare(strict_types=1);

namespace App\Enum\Alert;

enum AlertType: string
{
    case PRICE          = 'price';
    case PERCENT_CHANGE = 'percent_change';
    case VOLUME         = 'volume';
    case MARKET_CAP     = 'market_cap';
    case MOVING_AVERAGE = 'moving_average';
    case RSI            = 'rsi';

    /**
     * Used by Assert\Choice in requests.
     * PHP attributes only accept compile-time constants.
     */
    public const VALUES = [
        'price', 'percent_change', 'volume',
        'market_cap', 'moving_average', 'rsi',
    ];

    public function label(): string
    {
        return match ($this) {
            self::PRICE          => 'Price',
            self::PERCENT_CHANGE => '% Change',
            self::VOLUME         => 'Volume',
            self::MARKET_CAP     => 'Market Cap',
            self::MOVING_AVERAGE => 'Moving Average',
            self::RSI            => 'RSI',
        };
    }

    /**
     * Types where thresholdValue is a plain price/number (USD etc.)
     * @return bool
     */
    public function usesMonetaryThreshold(): bool
    {
        return in_array($this, [self::PRICE, self::MARKET_CAP], true);
    }

    /**
     * Types where thresholdValue is a percentage
     * @return bool
     */
    public function usesPercentThreshold(): bool
    {
        return in_array($this, [self::PERCENT_CHANGE, self::RSI], true);
    }
}
