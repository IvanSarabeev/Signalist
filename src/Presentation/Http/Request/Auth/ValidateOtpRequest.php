<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Auth;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ValidateOtpRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'OTP is required')]
        #[Assert\Type(Types::STRING)]
        #[Assert\Length(max: 6, maxMessage: 'OTP is too long')]
        public string $code,
    )
    { }
}
