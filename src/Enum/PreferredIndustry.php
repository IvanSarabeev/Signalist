<?php

declare(strict_types=1);

namespace App\Enum;

enum PreferredIndustry: string
{
    case TECHNOLOGY = '1';
    case HEALTHCARE = '2';
    case FINANCE = '3';
    case ENERGY = '4';
    case CONSUMER_GOODS = '5';

    public function toLabel(): string
    {
        return match ($this) {
            self::TECHNOLOGY => 'Technology',
            self::HEALTHCARE => 'Healthcare',
            self::FINANCE => 'Finance',
            self::ENERGY => 'Energy',
            self::CONSUMER_GOODS => 'Consumers Goods',
        };
    }
}
