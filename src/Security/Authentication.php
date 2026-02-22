<?php

declare(strict_types = 1);

namespace App\Security;

use App\DTO\Auth\RegisterDTO;
use App\DTO\Auth\SignInDTO;
use App\Entity\User;
use App\Enum\InvestmentGoal;
use App\Enum\NotificationType;
use App\Enum\PreferredIndustry;
use App\Enum\RiskTolerance;
use App\Exception\Security\EmailExistsException;
use App\Exception\Security\InvalidCredentialsException;
use App\Exception\Security\UserRegistrationException;
use App\Notification\NotificationDispatcher;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class Authentication
{
    private const AUTHENTICATION_PREFIX = "Authentication: ";

    public function __construct(
        private EntityManagerInterface      $entityManager,
        private UserRepository              $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private LoggerInterface             $logger,
        private NotificationDispatcher      $notificationDispatcher,
    ) {
    }

    /**
     * Persist the User to the system
     *
     * @param RegisterDTO $dto
     * @return User
     * @throws Exception
     */
    public function persistUserRegistration(RegisterDTO $dto): User
    {
        $existingUser = $this->userRepository->findOneByEmail($dto->email);

        if ($existingUser !== null) {
            throw new EmailExistsException();
        }

        $user = new User();

        $user->setFullName($dto->fullName);
        $user->setEmail($dto->email);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $dto->password)
        );
        $user->setCountry($dto->country);
        $user->setInvestmentGoal(InvestmentGoal::from($dto->investmentGoals));
        $user->setRiskTolerance(RiskTolerance::from($dto->riskTolerance));
        $user->setPreferredIndustry(PreferredIndustry::from($dto->preferredIndustry));

        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->notificationDispatcher->notify(
                NotificationType::USER_REGISTERED,
                $user
            );
        } catch (Exception $exception) {
            $this->logger->error(self::AUTHENTICATION_PREFIX . 'User registration failed: ', [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ]);

            throw new UserRegistrationException();
        }

        return $user;
    }

    /**
     * Authenticate the User credentials
     *
     * @param SignInDTO $dto
     * @return User
     * @throws InvalidCredentialsException - Throw an error
     */
    public function authenticateUser(SignInDTO $dto): User
    {
        $user = $this->userRepository->findOneByEmail($dto->email);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $dto->password)) {
            throw new InvalidCredentialsException();
        }

        return $user;
    }
}
