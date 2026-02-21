<?php

declare(strict_types=1);

namespace App\DTO\Otp;

use Symfony\Component\Validator\Constraints as Assert;

final class VerifyOtpRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 4, max: 4)]
    public string $otp;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $userId;
}
