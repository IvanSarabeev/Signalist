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

    public const VALUES = [
        'incomplete', 'incomplete_expired', 'trialing', 'active',
        'past_due', 'canceled', 'unpaid', 'paused',
    ];

    public function grantsAccess(): bool
    {
        return match ($this) {
            self::ACTIVE, self::TRIALING, self::PAST_DUE => true,
            default => false
        };
    }

    public function requiredPaymentAttention(): bool
    {
        return $this === self::PAST_DUE;
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::CANCELED, self::INCOMPLETE => true,
            default => false,
        };
    }
}
