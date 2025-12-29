<?php

declare(strict_types=1);

namespace App\Enum;

enum InvestmentGoal: string
{
    case GROWTH = '1';
    case INCOME = '2';
    case BALANCED = '3';
    case CONSERVATIVE = '4';

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
