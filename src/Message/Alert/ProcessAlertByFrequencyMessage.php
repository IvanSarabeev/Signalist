<?php

declare(strict_types=1);

namespace App\Message\Alert;

use App\Enum\Alert\AlertFrequency;

final readonly class ProcessAlertByFrequencyMessage
{
    public function __construct(public AlertFrequency $frequency)
    { }
}
