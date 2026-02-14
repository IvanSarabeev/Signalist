<?php

namespace App\Message\Auth;

final readonly class SendOtpMessage
{
    public function __construct(
        public int $userId,
        public string $otp,
    ) { }
}
