<?php

declare(strict_types=1);

namespace App\Enum\User;

enum InvestmentGoal: string
{
    case GROWTH       = 'growth';
    case INCOME       = 'income';
    case BALANCED     = 'balanced';
    case CONSERVATIVE = 'conservative';

    public const VALUES = ['growth', 'income', 'balanced', 'conservative'];

    public function label(): string
    {
        return match ($this) {
            self::GROWTH       => 'Growth',
            self::INCOME       => 'Income',
            self::BALANCED     => 'Balanced',
            self::CONSERVATIVE => 'Conservative',
        };
    }
}
