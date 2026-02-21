<?php

namespace App\Security\Otp;

use App\Exception\Security\ExpiredOtpException;
use App\Exception\Security\InvalidOtpCredentialsException;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

final readonly class OtpService
{
    public function __construct(
        private RequestStack $requestStack,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) { }

    /**
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function validateVerificationCode(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request->request->has('otp') && empty($request->request->getString('otp'))) {
            throw new InvalidOtpCredentialsException();
        }

        if ($request->request->has('user_id') && empty($request->request->getInt('user_id'))) {
            throw new InvalidOtpCredentialsException();
        }

        $userId = $request->request->getInt('user_id');
        $otp = $request->request->getString('otp');

        $user = $this->userRepository->findOneBy(['id' => $userId]);

        if (!$user) {
            throw new UserNotFoundException();
        }

        if ($user->getOtpHash() !== $otp || $user->getExpiredAt() < new DateTimeImmutable()) {
            throw new ExpiredOtpException();
        }

        $user->clearOtp();
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
