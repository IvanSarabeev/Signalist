<?php

declare(strict_types=1);

namespace App\Enum\User;

enum RiskTolerance: string
{
    case LOW    = 'low';
    case MEDIUM = 'medium';
    case HIGH   = 'high';

    public const VALUES = ['low', 'medium', 'high'];

    public function label(): string
    {
        return match ($this) {
            self::LOW    => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH   => 'High',
        };
    }
}
