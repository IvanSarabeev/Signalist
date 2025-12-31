<?php

declare(strict_types=1);

namespace App\Enum;

enum InvestmentGoal: string
{
    case GROWTH = 'growth';
    case INCOME = 'income';
    case BALANCED = 'balanced';
    case CONSERVATIVE = 'conservative';

    public function toLabel(): string
    {
        return match ($this) {
            self::GROWTH => 'Growth',
            self::INCOME => 'Income',
            self::BALANCED => 'Balanced',
            self::CONSERVATIVE => 'Conservative',
        };
    }
}
