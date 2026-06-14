<?php

declare(strict_types=1);

namespace App\Service\Alert;

use App\Entity\Alert;
use App\Entity\User;
use App\Presentation\Http\Request\Alert\CreateAlertRequest;
use App\Presentation\Http\Request\PaginatedRequest;
use App\Presentation\Http\Response\PaginatedResponse;

interface AlertServiceInterface
{
    public function getAlerts(User $user, PaginatedRequest $paginatedRequest): ?PaginatedResponse;

    public function createAlert(User $user, CreateAlertRequest $createAlertRequest): Alert;
}
