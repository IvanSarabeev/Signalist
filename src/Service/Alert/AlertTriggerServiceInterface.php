<?php

declare(strict_types=1);

namespace App\Service\Alert;

use App\Entity\Alert;

interface AlertTriggerServiceInterface
{
    public function handleTrigger(Alert $alert): void;
}
