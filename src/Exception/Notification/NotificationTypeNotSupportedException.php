<?php

namespace App\Exception\Notification;

use LogicException;
use Throwable;

final class NotificationTypeNotSupportedException extends LogicException
{
    public function __construct(string $message = "Notification type not supported", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
