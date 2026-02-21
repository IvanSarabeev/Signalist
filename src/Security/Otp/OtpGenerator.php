<?php

namespace App\Security\Otp;

use Random\RandomException;

final class OtpGenerator implements GeneratorInterface
{
    /**
     * @throws RandomException
     */
    public function generate(): string
    {
        return (string) random_int(1000, 9999);
    }

    public function hash(string $otp): string
    {
        return password_hash($otp, PASSWORD_BCRYPT);
    }

    public function verify(string $otp, string $hash): bool
    {
        return password_verify($otp, $hash);
    }

}
