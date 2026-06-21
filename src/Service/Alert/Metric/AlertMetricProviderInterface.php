<?php

declare(strict_types=1);

namespace App\Service\Alert\Metric;

use App\Entity\Alert;

interface AlertMetricProviderInterface
{
    public function getCurrentMetric(Alert $alert): float;
}
