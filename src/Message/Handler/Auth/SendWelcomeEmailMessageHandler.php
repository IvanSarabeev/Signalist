<?php

namespace App\Message\Handler\Auth;

use App\Message\Auth\SendWelcomeEmailMessage;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
final readonly class SendWelcomeEmailMessageHandler
{
    public function __construct(
        private UserRepository  $userRepository,
        private MailerInterface $mailer,
        private Email $email,
    ) { }

    public function __invoke(SendWelcomeEmailMessage $message): void
    {
        $user = $this->userRepository->find($message->userId);

        if (!$user) {
            return;
        }

        /**
         * TODO: Create Email Service
         */
    }
}
