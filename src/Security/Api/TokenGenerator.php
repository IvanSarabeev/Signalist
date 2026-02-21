<?php

declare(strict_types=1);

namespace App\Security\Api;

use Firebase\JWT\JWT;

class TokenGenerator
{
    private string $jwtSecret;
    private int $ttl;

    public function __construct(string $jwtSecret, int $ttl)
    {
        $this->jwtSecret = $jwtSecret;
        $this->ttl = $ttl;
    }

    public function generate(string|int $userId, array $additionalClaims = []): string
    {
        $now = time();
        $payload = array_merge($additionalClaims, [
            'sub' => $userId,           // User identifier
            'iat' => $now,              // Issued at
            'exp' => $now + $this->ttl, // Expiration Time
        ]);

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
}
