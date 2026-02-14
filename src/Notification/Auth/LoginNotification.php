<?php

namespace App\Notification\Auth;

use App\Entity\User;
use App\Enum\NotificationType;
use App\Message\Auth\SendOtpMessage;
use App\Notification\NotificationInterface;
use App\Security\OtpGenerator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class LoginNotification implements NotificationInterface
{
    public function __construct(
        private OtpGenerator $otpGenerator,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
    ) { }

    public function supports(NotificationType $type): bool
    {
        return $type === NotificationType::LOGIN_OTP;
    }

    public function notify(User $user): void
    {
        $otp = $this->otpGenerator->generate();

        $user->setOtpHash($this->otpGenerator->hash($otp));
        $user->setOtpExpiresAt(new DateTimeImmutable('+5 minutes'));

        $this->entityManager->flush();

        $this->bus->dispatch(
            new SendOtpMessage($user->getId(), $otp)
        );
    }

}
