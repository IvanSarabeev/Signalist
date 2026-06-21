<?php

namespace App\Security\Otp;

use App\Entity\User;
use App\Presentation\Http\Exception\Security\ExpiredOtpException;
use App\Presentation\Http\Exception\Security\UserNotFoundException;
use App\Presentation\Http\Request\Auth\ValidateOtpRequest;
use App\Security\Token\TokenManager;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class OtpService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenManager           $tokenGenerator,
    ) { }

    /**
     * @param ValidateOtpRequest $otpRequest
     * @return void
     */
    public function validateVerificationCode(ValidateOtpRequest $otpRequest): void
    {
        $token = $this->tokenGenerator->validateToken($otpRequest->code);

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

        if (!hash_equals($user->getOtpHash(), $otpRequest->code)) {
            throw new ExpiredOtpException();
        }

        $user->clearOtp();
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
