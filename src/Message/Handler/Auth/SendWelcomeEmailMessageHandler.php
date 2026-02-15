<?php

namespace App\Message\Handler\Auth;

use App\Message\Auth\SendWelcomeEmailMessage;
use App\Repository\UserRepository;
use App\Service\Mailer\EmailFactory;
use App\Service\Mailer\EmailService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsMessageHandler]
final readonly class SendWelcomeEmailMessageHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailFactory $emailFactory,
        private EmailService $emailService,
        private LoggerInterface $logger,
    ) { }

    /**
     * @param SendWelcomeEmailMessage $message
     * @return void
     * @throws Throwable
     * @throws TransportExceptionInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(SendWelcomeEmailMessage $message): void
    {
        try {

            $user = $this->userRepository->find($message->userId);

            if (!$user) {
                throw new UserNotFoundException();
            }

            $email = $this->emailFactory->createWelcomeEmail(
                $user->getEmail(),
                $user->getFullName()
            );
            $this->emailService->send($email);
        } catch (Throwable $exception) {
            $this->logger->error('Failed to proceed ' . SendWelcomeEmailMessage::class, [
                'userId' => $message->userId,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
