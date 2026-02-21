<?php

namespace App\Security\Otp;

use App\DTO\Otp\VerifyOtpRequest;
use App\Exception\Security\ExpiredOtpException;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

final readonly class OtpService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) { }

    /**
     * @param VerifyOtpRequest $dto
     * @return void
     */
    public function validateVerificationCode(VerifyOtpRequest $dto): void
    {
        $user = $this->userRepository->findOneBy(['id' => $dto->userId]);

        if (!$user) {
            throw new UserNotFoundException();
        }

        if ($user->getOtpHash() !== $dto->otp || $user->getExpiredAt() < new DateTimeImmutable()) {
            throw new ExpiredOtpException();
        }

        $user->clearOtp();
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
