<?php

declare(strict_types=1);

namespace App\Service\Alert;

use App\Entity\Alert;
use App\Enum\Alert\AlertCondition;
use DateTimeImmutable;

final readonly class AlertEvaluationService implements AlertEvaluationServiceInterface
{
    /**
     * Evaluate the alert condition against the freshly fetched metric.
     *
     * For CROSSES_ABOVE / CROSSES_BELOW, a "previous" price is required.
     * We use stock.cachedPrice — the last price stored in the local DB — as a
     * reasonable approximation of the value at the previous scheduler tick.
     *
     * Limitation: crossover detection is approximate. If the price passes the
     * threshold and reverses between two scheduler runs, the crossing is missed.
     * Accurate crossover tracking would require persisting the previous metric
     * in the Alert entity itself.
     */
    public function isAlertConditionCorrect(Alert $alert, float $currentMetric): bool
    {
        $threshold = (float) $alert->getThresholdValue();
        $condition = $alert->getConditionQuality();

        if ($condition === AlertCondition::CROSSES_ABOVE) {
            // The stock was below (or at) the threshold before, and is now above it
            $previousPrice = (float) $alert->getStock()->getCachedPrice();

            return $previousPrice < $threshold && $currentMetric >= $threshold;
        }

        if ($condition === AlertCondition::CROSSES_BELOW) {
            // The stock was above (or at) the threshold before, and is now below it
            $previousPrice = (float) $alert->getStock()->getCachedPrice();

            return $previousPrice > $threshold && $currentMetric <= $threshold;
        }

        // All other conditions (gt, gte, lt, lte, eq) are handled by the enum
        return $condition->evaluate($currentMetric, $threshold);
    }

    /**
     * Check whether the cooldown period has elapsed since lastTriggeredAt.
     *
     * A null lastTriggeredAt means the alert has never fired — always eligible.
     */
    public function isCooldownExpired(Alert $alert): bool
    {
        if ($alert->getLastTriggeredAt() === null) {
            return true;
        }

        $now     = new DateTimeImmutable();
        $elapsed = $now->getTimestamp() - $alert->getLastTriggeredAt()->getTimestamp();

        return $elapsed >= $alert->getFrequency()->cooldownSeconds();
    }
}
