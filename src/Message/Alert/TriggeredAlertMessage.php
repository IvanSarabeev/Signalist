<?php

declare(strict_types=1);

namespace App\Message\Alert;

final readonly class TriggeredAlertMessage
{
    public function __construct(public int $alertId, public float $currentMetric, public string $triggeredAt)
    { }
}
