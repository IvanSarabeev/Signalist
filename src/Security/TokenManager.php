<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\RefreshTokens;
use App\Entity\User;
use App\Exception\Token\TokenNotFoundException;
use App\Exception\Token\UnexpectedTokenException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Random\RandomException;

final readonly class TokenManager
{
    public function __construct(
        private string $jwtSecret,
        private int $accessTtl,
        private int $refreshTtl,
        private EntityManagerInterface $entityManager
    )
    {}

    /**
     * @param User $user
     * @param array $additionalClaims
     * @return string
     */
    public function generateAccessToken(User $user, array $additionalClaims = []): string
    {
        $now = time();
        $payload = array_merge($additionalClaims, [
            'sub'   => $user->getUserIdentifier(), // User identifier
            'roles' => $user->getRoles(),
            'iat'   => $now, // Issued at
            'exp'   => $now + $this->accessTtl, // Expiration Time
        ]);

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    /**
     * Refresh an User token
     *
     * @param int $userId
     * @return string
     * @throws RandomException
     */
    public function refreshToken(int $userId): string
    {
        $token = bin2hex(random_bytes(64));

        $expiresAt = (new DateTimeImmutable())->modify("+$this->refreshTtl seconds");

        $refreshToken = new RefreshTokens();
        $refreshToken->setUserId($userId);
        $refreshToken->setToken($token);
        $refreshToken->setExpiresAt($expiresAt);

        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        return $token;
    }

    /**
     * Validate the existing token
     *
     * @param string $token
     * @return RefreshTokens|null
     */
    public function validateToken(string $token): ?RefreshTokens
    {
        if (!$token) {
            throw new TokenNotFoundException();
        }

        $refreshToken = $this->entityManager->getRepository(RefreshTokens::class)
            ->findOneBy(['token' => $token, 'revoked' => false]);

        if (!$refreshToken) {
            throw new TokenNotFoundException();
        }

        if ($refreshToken->getExpiresAt() < new DateTimeImmutable()) {
            throw new UnexpectedTokenException();
        }

        return $refreshToken;
    }

    /**
     * @param RefreshTokens $refreshTokens
     * @return void
     */
    public function revokeRefreshToken(RefreshTokens $refreshTokens): void
    {
        $refreshTokens->setRevoked(true);
        $this->entityManager->flush();
    }
}
