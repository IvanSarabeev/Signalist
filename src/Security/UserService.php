<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\UserRepository;
use App\Security\Session\Session;
use App\Entity\User;

final readonly class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private Session $sessionService,
    )
    { }

    public function getAuthenticatedUser(): null|User
    {
        $authSession = $this->sessionService->getAuthentication();

        if (!$authSession || !isset($authSession['id'])) {
            return null;
        }

        return $this->userRepository->findOneById($authSession['id']);
    }
}
