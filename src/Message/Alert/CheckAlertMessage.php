<?php

declare(strict_types=1);

namespace App\Message\Alert;

final readonly class CheckAlertMessage
{
    public function __construct(public int $alertId)
    { }
}
