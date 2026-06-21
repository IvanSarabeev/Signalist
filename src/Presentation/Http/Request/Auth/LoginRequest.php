<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Auth;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class LoginRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Invalid credentials.')]
        #[Assert\Type(Types::STRING, message: 'This value is not a valid email address.')]
        #[Assert\Email(message: 'The email {{ value }} is not a valid email address.')]
        public string $email,

        #[Assert\NotBlank(message: 'Invalid credentials.')]
        #[Assert\Type(Types::STRING, message: 'This value is not a valid password.')]
        #[Assert\Length(min: 6, minMessage: 'The password strength is too low. Please use a stronger password.')]
        #[Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_MEDIUM)]
        public string $password,
    )
    { }
}
