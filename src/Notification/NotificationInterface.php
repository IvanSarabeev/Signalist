<?php

namespace App\Notification;

use App\Entity\User;
use App\Enum\NotificationType;

interface NotificationInterface
{
    public function supports(NotificationType $type): bool;

    public function notify(User $user): void;
}
