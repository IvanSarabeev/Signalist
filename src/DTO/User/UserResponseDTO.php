<?php

namespace App\DTO\User;

use App\Enum\InvestmentGoal;
use App\Enum\PreferredIndustry;
use App\Enum\RiskTolerance;
use DateTimeImmutable;

final class UserResponseDTO
{
    public function __construct(
        public ?string            $email,
        public ?string            $fullName,
        public ?string            $country,
        public ?InvestmentGoal    $investmentGoal,
        public ?PreferredIndustry $preferredIndustry,
        public ?RiskTolerance     $riskTolerance,
        public ?DateTimeImmutable $createdAt,
    ) { }
}
