<?php

declare(strict_types=1);

namespace App\Security\Token;

use App\Entity\RefreshTokens;
use App\Entity\User;
use Random\RandomException;

interface TokenManagerInterface
{
    public function generateAccessToken(User $user, array $additionalClaims = []): string;

    /**
     * @throws RandomException
     */
    public function refreshToken(int $userId): string;

    public function validateToken(string $token): ?RefreshTokens;

    public function revokeRefreshToken(RefreshTokens $refreshTokens): void;
}
