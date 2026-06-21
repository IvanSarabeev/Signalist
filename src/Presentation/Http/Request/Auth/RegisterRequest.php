<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Auth;

use App\Enum\User\InvestmentGoal;
use App\Enum\User\PreferredIndustry;
use App\Enum\User\RiskTolerance;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegisterRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'The full name is required.')]
        #[Assert\Type(Types::STRING, message: 'The full name is not valid.')]
        #[Assert\Length(min: 4, max: 120, minMessage: 'The full name is too short.', maxMessage: 'The full name is too long.')]
        public string $fullName,

        #[Assert\NotBlank(message: 'The email is required.')]
        #[Assert\Type(Types::STRING, message: 'This value is not a valid email address.')]
        #[Assert\Email(message: 'The email {{ value }} is not a valid email address.')]
        public string $email,

        #[Assert\NotBlank(message: 'The password is required.')]
        #[Assert\Type(Types::STRING, message: 'This value is not a valid password.')]
        #[Assert\Length(min: 6, minMessage: 'The password strength is too low. Please use a stronger password.')]
        #[Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_WEAK)]
        public string $password,

        #[Assert\NotBlank(message: 'The country is required.')]
        #[Assert\Country]
        public string $country,

        #[Assert\NotBlank(message: 'The investment goal is required.')]
        #[Assert\Choice(
            choices: InvestmentGoal::VALUES,
            message: 'Invalid investment goal.'
        )]
        public string $investmentGoals,

        #[Assert\NotBlank(message: 'The risk tolerance is required.')]
        #[Assert\Choice(
            choices: RiskTolerance::VALUES,
            message: 'Invalid risk tolerance.'
        )]
        public string $riskTolerance,

        #[Assert\NotBlank(message: 'The preferred industry is required.')]
        #[Assert\Choice(
            choices: PreferredIndustry::VALUES,
            message: 'Invalid preferred industry.'
        )]
        public string $preferredIndustry
    )
    { }

    public function getInvestmentGoal(): InvestmentGoal
    {
        return InvestmentGoal::from($this->investmentGoals);
    }

    public function getRiskTolerance(): RiskTolerance
    {
        return RiskTolerance::from($this->riskTolerance);
    }

    public function getPreferredIndustry(): PreferredIndustry
    {
        return PreferredIndustry::from($this->preferredIndustry);
    }
}
