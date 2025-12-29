<?php

declare(strict_types=1);

namespace App\Enum;

enum RiskTolerance: string
{
    case LOW = '1';
    case MEDIUM = '2';
    case HIGH = '3';
}
