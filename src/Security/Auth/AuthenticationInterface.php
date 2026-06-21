<?php

declare(strict_types=1);

namespace App\Security\Auth;

use App\Entity\User;
use App\Presentation\Http\Request\Auth\LoginRequest;
use App\Presentation\Http\Request\Auth\RegisterRequest;

interface AuthenticationInterface
{
    public function persistUserRegistration(RegisterRequest $registerRequest): void;

    public function authenticateUser(LoginRequest $loginRequest): User;
}
