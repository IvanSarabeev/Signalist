<?php

declare(strict_types=1);

namespace App\Security\Auth;

use App\Entity\User;
use App\Notification\NotificationDispatcher;
use App\Presentation\Http\Exception\Security\EmailExistsException;
use App\Presentation\Http\Exception\Security\InvalidCredentialsException;
use App\Presentation\Http\Exception\Security\UserRegistrationException;
use App\Presentation\Http\Request\Auth\LoginRequest;
use App\Presentation\Http\Request\Auth\RegisterRequest;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class Authentication implements AuthenticationInterface
{
    private const AUTHENTICATION_PREFIX = "Authentication: ";

    public function __construct(
        private EntityManagerInterface      $entityManager,
        private UserRepository              $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private LoggerInterface             $logger,
        private NotificationDispatcher      $notificationDispatcher,
    )
    { }

    /**
     * Persist the User to the system
     *
     * @param RegisterRequest $registerRequest
     * @return void
     */
    public function persistUserRegistration(RegisterRequest $registerRequest): void
    {
        $existingUser = $this->userRepository->findOneByEmail($registerRequest->email);

        if ($existingUser !== null) {
            throw new EmailExistsException();
        }

        $user = new User();

        $user->setFullName($registerRequest->fullName);
        $user->setEmail($registerRequest->email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $registerRequest->password));
        $user->setCountry($registerRequest->country);
        $user->setInvestmentGoal($registerRequest->getInvestmentGoal());
        $user->setRiskTolerance($registerRequest->getRiskTolerance());
        $user->setPreferredIndustry($registerRequest->getPreferredIndustry());

        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Commented out due to low service limit
//            $this->notificationDispatcher->notify(NotificationType::USER_REGISTERED, $user);
        } catch (Exception $exception) {
            $this->entityManager->rollback();

            $this->logger->error(self::AUTHENTICATION_PREFIX . 'User registration failed: ', [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
            ]);

            throw new UserRegistrationException();
        }
    }

    /**
     * Authenticate the User credentials
     *
     * @param LoginRequest $loginRequest
     * @return User
     */
    public function authenticateUser(LoginRequest $loginRequest): User
    {
        $user = $this->userRepository->findOneByEmail($loginRequest->email);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $loginRequest->password)) {
            throw new InvalidCredentialsException();
        }

        return $user;
    }
}
