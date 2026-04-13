<?php

declare(strict_types=1);

namespace App\Mapper\User;

use App\DTO\User\UserResponseDTO;
use App\Entity\User;

final class UserMapper
{
    public function toDTO(User $user): UserResponseDTO
    {
        return new UserResponseDTO(
            $user->getEmail(),
            $user->getFullName(),
            $user->getCountry(),
            $user->getInvestmentGoal(),
            $user->getPreferredIndustry(),
            $user->getRiskTolerance(),
            $user->getCreatedAt(),
        );
    }
}
