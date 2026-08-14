<?php

declare(strict_types=1);

namespace App\Enum\Billing;

enum Plan: string
{
    case OPEN = 'open';
    case PLUS = 'plus';
    case PRO  = 'pro';

    /**
     * Used by Assert\Choice in requests.
     */
    public const VALUES = ['open', 'plus', 'pro'];

    /**
     * Only these may be bought through Checkout. OPEN is a cancellation, not a purchase.
     */
    public const PURCHASABLE = ['plus', 'pro'];

    /**
     * Check whether the service is paid
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this !== self::OPEN;
    }

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::PLUS => 'Plus',
            self::PRO  => 'Pro'
        };
    }

    /**
     * Ordering for upgrade/downgrade comparisons.
     */
    public function rank(): int
    {
        return match ($this) {
            self::OPEN => 0,
            self::PLUS => 1,
            self::PRO  => 2,
        };
    }

    public function isUpgradeFrom(self $current): bool
    {
        return $this->rank() > $current->rank();
    }
}
