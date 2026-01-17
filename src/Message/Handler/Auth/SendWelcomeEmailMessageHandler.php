<?php

namespace App\Message\Handler\Auth;

use App\Message\Auth\SendWelcomeEmailMessage;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendWelcomeEmailMessageHandler
{
    public function __construct(
        private UserRepository  $userRepository,
    ) { }

    public function __invoke(SendWelcomeEmailMessage $message): void
    {
        $user = $this->userRepository->find($message->userId);

        if (!$user) {
            echo 'Unable to find user with ID: ' . $message->userId;
        }

        /**
         * TODO: Create Email Service
         */
    }
}
