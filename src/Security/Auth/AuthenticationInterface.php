<?php

declare(strict_types=1);

namespace App\Security\Auth;

use App\DTO\Auth\RegisterDTO;
use App\Entity\User;
use App\Presentation\Http\Request\Auth\LoginRequest;

interface AuthenticationInterface
{
    public function persistUserRegistration(RegisterDTO $dto): User;

    public function authenticateUser(LoginRequest $loginRequest): User;
}
