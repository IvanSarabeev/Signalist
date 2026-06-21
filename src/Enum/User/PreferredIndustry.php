<?php

declare(strict_types=1);

namespace App\Enum\User;

enum PreferredIndustry: string
{
    case TECHNOLOGY     = 'technology';
    case HEALTHCARE     = 'healthcare';
    case FINANCE        = 'finance';
    case ENERGY         = 'energy';
    case CONSUMER_GOODS = 'consumerGoods';

    public const VALUES = ['technology', 'healthcare', 'finance', 'energy', 'consumerGoods'];
}
