<?php

namespace App\Notification;

use App\Entity\User;
use App\Enum\NotificationType;
use App\Exception\Notification\NotificationTypeNotSupportedException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class NotificationDispatcher
{
    /**
     * @param iterable<NotificationInterface> $notifications
     */
    public function __construct(
        #[AutowireIterator('app.notification')]
        private iterable $notifications
    )
    { }

    public function notify(NotificationType $type, User $user): void
    {
        foreach ($this->notifications as $notification) {
            if ($notification->supports($type)) {
                $notification->notify($user);
                return;
            }
        }

       throw new NotificationTypeNotSupportedException();
    }
}
