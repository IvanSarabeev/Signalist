<?php

namespace App\Security\Otp;

use App\DTO\Otp\VerifyOtpRequest;
use App\Entity\User;
use App\Exception\Security\ExpiredOtpException;
use App\Exception\Security\UserNotFoundException;
use App\Security\TokenGenerator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class OtpService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenGenerator $tokenGenerator,
    ) { }

    /**
     * @param VerifyOtpRequest $dto
     * @return void
     */
    public function validateVerificationCode(VerifyOtpRequest $dto): void
    {
        $token = $this->tokenGenerator->validateToken($dto->token);

        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['id' => $token->getUserId()]);
        if (!$user) {
            throw new UserNotFoundException();
        }

        if (
            $user->getOtpExpiresAt() === null ||
            $user->getOtpExpiresAt() < new DateTimeImmutable()
        ) {
            throw new ExpiredOtpException();
        }

        if (!hash_equals($user->getOtpHash(), $dto->otp)) {
            throw new ExpiredOtpException();
        }

        $user->clearOtp();
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
