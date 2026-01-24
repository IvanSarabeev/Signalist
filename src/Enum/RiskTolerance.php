<?php

declare(strict_types=1);

namespace App\Enum;

use InvalidArgumentException;

enum RiskTolerance: string implements BaseEnumInterface
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public static function fromLabel(string $label): RiskTolerance
    {
        return match ($label) {
            'Low' => self::LOW,
            'Medium' => self::MEDIUM,
            'High' => self::HIGH,
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
