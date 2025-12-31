<?php

declare(strict_types=1);

namespace App\Enum;

enum RiskTolerance: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
}
