<?php

namespace App\Security;

interface GeneratorInterface
{
    public function generate(): string;

    public function hash(string $otp): string;

    public function verify(string $otp, string $hash): bool;
}
