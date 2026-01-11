<?php

declare(strict_types = 1);

namespace App\Service;

use App\DTO\Auth\RegisterDTO;
use App\DTO\Auth\SignInDTO;
use App\Entity\User;
use App\Enum\InvestmentGoal;
use App\Enum\NotificationType;
use App\Enum\PreferredIndustry;
use App\Enum\RiskTolerance;
use App\Exception\Security\EmailExistsException;
use App\Exception\Security\InvalidCredentialsException;
use App\Exception\Security\UserAlreadyExistsException;
use App\Exception\Security\UserRegistrationFailedException;
use App\Notification\NotificationDispatcher;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Throwable;

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
        $user->setInvestmentGoal(InvestmentGoal::from($dto->investmentGoal));
        $user->setRiskTolerance(RiskTolerance::from($dto->riskTolerance));
        $user->setPreferredIndustry(PreferredIndustry::from($dto->preferredIndustry));

        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->notificationDispatcher->notify(NotificationType::USER_REGISTERED, $user);
        } catch (UserAlreadyExistsException $exception) {
            $this->logger->error(self::AUTHENTICATION_PREFIX . 'User registration failed: duplicated email', [
                'email' => $dto->email,
                'exception' => $exception,
            ]);

            throw new UserAlreadyExistsException('Email already registered.');
        } catch (Throwable $throwable) {
            $this->logger->error(self::AUTHENTICATION_PREFIX . 'User registration failed: ', [
                'exception' => $throwable,
            ]);

            throw new UserRegistrationFailedException('Unable to register user.');
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
