<?php

declare(strict_types=1);

namespace App\Enum;

use InvalidArgumentException;

enum InvestmentGoal: string implements BaseEnumInterface
{
    case GROWTH = 'growth';
    case INCOME = 'income';
    case BALANCED = 'balanced';
    case CONSERVATIVE = 'conservative';

    /**
     * @param string $label
     * @return InvestmentGoal
     */
    public static function fromLabel(string $label): InvestmentGoal
    {
        return match ($label) {
            'Growth' => self::GROWTH,
            'Income' => self::INCOME,
            'Balanced' => self::BALANCED,
            'Conservative' => self::CONSERVATIVE,
            default => throw new InvalidArgumentException('Invalid label')
        };
    }

    /**
     * @return array
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
