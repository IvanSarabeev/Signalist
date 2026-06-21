<?php

declare(strict_types=1);

namespace App\Service\Alert;

use App\Entity\Alert;

interface AlertEvaluationServiceInterface
{
    public function isAlertConditionCorrect(Alert $alert, float $currentMetric): bool;

    public function isCooldownExpired(Alert $alert): bool;

    public function handleTrigger(Alert $alert): void;
}
