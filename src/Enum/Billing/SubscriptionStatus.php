<?php

namespace App\Enum\Billing;

enum SubscriptionStatus: string
{
    case INCOMPLETE = 'incomplete';
    case TRIALING   = 'trialing';
    case ACTIVE     = 'active';
    case PAST_DUE   = 'past_due';
    case CANCELED   = 'canceled';
    case UNPAID     = 'unpaid';

    public function grantsAccess(): bool
    {
        return match ($this) {
            self::ACTIVE,
            self::TRIALING,
            self::PAST_DUE => true,
            default        => false
        };
    }
}
