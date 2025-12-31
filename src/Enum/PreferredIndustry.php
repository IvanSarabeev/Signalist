<?php

declare(strict_types=1);

namespace App\Enum;

enum PreferredIndustry: string
{
    case TECHNOLOGY = 'technology';
    case HEALTHCARE = 'healthcare';
    case FINANCE = 'finance';
    case ENERGY = 'energy';
    case CONSUMER_GOODS = 'consumerGoods';

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
