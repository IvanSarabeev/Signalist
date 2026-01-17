<?php

namespace App\Notification;

use App\Entity\User;
use App\Enum\NotificationType;
use App\Exception\Notification\NotificationTypeNotSupportedException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class NotificationDispatcher
{
    public function __construct(
        private NotificationInterface $notifications,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function notify(NotificationType $type, User $user): void
    {
        foreach ($this->notifications as $notification) {
            if ($notification->supports($type)) {
                $notification->notify($user);
            }
        }

        throw new NotificationTypeNotSupportedException();
    }
}
