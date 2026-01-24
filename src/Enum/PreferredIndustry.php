<?php

declare(strict_types=1);

namespace App\Enum;

use InvalidArgumentException;

enum PreferredIndustry: string implements BaseEnumInterface
{
    case TECHNOLOGY = 'technology';
    case HEALTHCARE = 'healthcare';
    case FINANCE = 'finance';
    case ENERGY = 'energy';
    case CONSUMER_GOODS = 'consumerGoods';

    /**
     * @param string $label
     * @return PreferredIndustry
     */
    public static function fromLabel(string $label): PreferredIndustry
    {
        return match($label) {
            'Technology' => self::TECHNOLOGY,
            'Healthcare' => self::HEALTHCARE,
            'Finance' => self::FINANCE,
            'Energy' => self::ENERGY,
            'Consumers Goods' => self::CONSUMER_GOODS,
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
