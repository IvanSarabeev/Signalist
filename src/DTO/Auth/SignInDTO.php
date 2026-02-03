<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class SignInDTO
{
    #[Assert\NotBlank(message: 'Please enter your email')]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email address.')]
    public string $email;

    #[Assert\NotBlank(message: 'Please enter your password')]
    #[Assert\Length(
        min: 6,
        minMessage: 'The password must be at least 6 characters long.',
    )]
//    #[Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_MEDIUM)]
    public string $password;
}
