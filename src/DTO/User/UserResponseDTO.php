<?php

namespace App\DTO\User;

use App\Enum\User\InvestmentGoal;
use App\Enum\User\PreferredIndustry;
use App\Enum\User\RiskTolerance;
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
