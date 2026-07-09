<?php

declare(strict_types=1);

namespace App\Enum\Finnhub;

enum CategoryNews: string
{
    case GENERAL = 'general';
    case FOREX   = 'forex';
    case CRYPTO  = 'crypto';
    case MERGER  = 'merger';
}
