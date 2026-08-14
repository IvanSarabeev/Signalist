<?php

declare(strict_types=1);

namespace App\Enum\Billing;

enum Plan: string
{
    case OPEN = 'open';
    case PLUS = 'plus';
    case PRO  = 'pro';

    public const VALUES = ['open', 'plus', 'pro'];

    /**
     * Check whether the service is paid
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this !== self::OPEN;
    }
}
