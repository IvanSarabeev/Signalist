<?php

declare(strict_types=1);

namespace App\Presentation\Http\Exception\Services\Alert;

use App\Enum\Alert\AlertType;

final class UnsupportedAlertTypeException extends \RuntimeException
{
    public function __construct(AlertType $alertType) {
        parent::__construct(
            sprintf(
                'Alert type "%s" (%s) has no metric-fetching implementation yet',
                $alertType->value,
                $alertType->label()
            ),
        );
    }
}
