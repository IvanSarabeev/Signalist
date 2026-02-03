<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use App\Enum\InvestmentGoal;
use App\Enum\PreferredIndustry;
use App\Enum\RiskTolerance;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class RegisterDTO
{
    #[Assert\Type('string')]
    #[Assert\NotBlank(message: 'The full name is required.')]
    public string $fullName;

    #[Assert\Type('string')]
    #[Assert\NotBlank(message: 'The email is required.')]
    #[Assert\Email(message: 'The email is not a valid email address.')]
    public string $email;

    #[Assert\Type('string')]
    #[Assert\NotBlank(message: 'The password is required.')]
//    #[Assert\PasswordStrength(
//        minScore: Assert\PasswordStrength::STRENGTH_WEAK,
//        message: 'The password must contain at least special'
//    )]
    #[Assert\Length(
        min: 6,
        minMessage: 'The password must be at least 6 characters long.',
    )]
    public string $password;

    #[Assert\Country]
    #[Assert\NotBlank(message: 'The country is required.')]
    public string $country;

    #[Assert\NotBlank(message: 'The investment goal is required.')]
    #[Assert\Choice(callback: [InvestmentGoal::class, 'getValues'], message: 'The investment goal is required.')]
    #[SerializedName('investmentGoals')]
    public string $investmentGoals;

    #[Assert\NotBlank(message: 'The risk tolerance is required.')]
    #[Assert\Choice(callback: [RiskTolerance::class, 'getValues'], message: 'The risk tolerance is required.')]
    #[SerializedName('riskTolerance')]
    public string $riskTolerance;

    #[Assert\NotBlank(message: 'The preferred industry is required.')]
    #[Assert\Choice(callback: [PreferredIndustry::class, 'getValues'], message: 'The preferred industry is required.')]
    #[SerializedName('preferredIndustry')]
    public string $preferredIndustry;
}
