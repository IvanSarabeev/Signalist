<?php

declare(strict_types=1);

namespace App\Security\Auth;

use App\DTO\Auth\RegisterDTO;
use App\DTO\Auth\SignInDTO;
use App\Entity\User;

interface AuthenticationInterface
{
    public function persistUserRegistration(RegisterDTO $dto): User;

    public function authenticateUser(SignInDTO $dto): User;
}
