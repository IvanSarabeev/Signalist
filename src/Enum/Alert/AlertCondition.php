<?php

namespace App\Enum\Alert;

enum AlertCondition: string
{
    case GREATER_THAN          = 'gt';
    case GREATER_THAN_OR_EQUAL = 'gte';
    case LESS_THAN             = 'lt';
    case LESS_THAN_OR_EQUAL    = 'lte';
    case EQUALS                = 'eq';
    case CROSSES_ABOVE         = 'crosses_above';
    case CROSSES_BELOW         = 'crosses_below';

    /**
     * Used by Assert\Choice in requests.
     * PHP attributes only accept compile-time constants.
     */
    public const VALUES = [
        'gt', 'gte', 'lt', 'lte', 'eq', 'crosses_above', 'crosses_below',
    ];


    public function label(): string
    {
        return match($this) {
            self::GREATER_THAN          => 'Greater than (>)',
            self::GREATER_THAN_OR_EQUAL => 'Greater than or equal (≥)',
            self::LESS_THAN             => 'Less than (<)',
            self::LESS_THAN_OR_EQUAL    => 'Less than or equal (≤)',
            self::EQUALS                => 'Equals (=)',
            self::CROSSES_ABOVE         => 'Crosses above (↑)',
            self::CROSSES_BELOW         => 'Crosses below (↓)',
        };
    }

    public function symbol(): string
    {
        return match($this) {
            self::GREATER_THAN          => '>',
            self::GREATER_THAN_OR_EQUAL => '≥',
            self::LESS_THAN             => '<',
            self::LESS_THAN_OR_EQUAL    => '≤',
            self::EQUALS                => '=',
            self::CROSSES_ABOVE         => '↑',
            self::CROSSES_BELOW         => '↓',
        };
    }

    /**
     * Evaluate whether $currentValue satisfies this condition against $threshold.
     * Note: CROSSES_ABOVE / CROSSES_BELOW require external state tracking.
     */
    public function evaluate(float $currentValue, float $threshold): bool
    {
        return match($this) {
            self::GREATER_THAN          => $currentValue >  $threshold,
            self::GREATER_THAN_OR_EQUAL => $currentValue >= $threshold,
            self::LESS_THAN             => $currentValue <  $threshold,
            self::LESS_THAN_OR_EQUAL    => $currentValue <= $threshold,
            self::EQUALS                => abs($currentValue - $threshold) < 0.0001,
            // Crossover logic must be handled at the service layer
            self::CROSSES_ABOVE,
            self::CROSSES_BELOW         => false,
        };
    }
}
