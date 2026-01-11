<?php

namespace App\Notification\Auth;

use App\Entity\User;
use App\Enum\NotificationType;
use App\Message\Auth\SendWelcomeEmailMessage;
use App\Notification\NotificationInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class WelcomeEmailNotification implements NotificationInterface
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public function supports(NotificationType $type): bool
    {
        return $type === NotificationType::USER_REGISTERED;
    }

    /**
     * Notify User
     *
     * @param User $user
     * @return void
     * @throws ExceptionInterface
     */
    public function notify(User $user): void
    {
        $this->bus->dispatch(new SendWelcomeEmailMessage($user->getId()));
    }
}
