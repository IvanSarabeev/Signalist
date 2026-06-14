<?php

declare(strict_types=1);

namespace App\Service\Alert;

use App\Entity\User;
use App\Presentation\Http\Request\PaginatedRequest;
use App\Presentation\Http\Response\PaginatedResponse;
use App\Repository\AlertRepository;

final readonly class AlertService implements AlertServiceInterface
{
    public function __construct(
        private AlertRepository $alertRepository,
    )
    { }

    public function getAlerts(User $user, PaginatedRequest $paginatedRequest): ?PaginatedResponse
    {
        $total = $this->alertRepository->countUserAlerts($user);

        if ($total === 0) {
            return null;
        }

        $alerts = $this->alertRepository->findUserAlertItems(
            $user,
            $paginatedRequest->limit,
            $paginatedRequest->getOffset()
        );

        return new PaginatedResponse(
            items:       $alerts,
            total:       $total,
            page:        $paginatedRequest->page,
            limit:       $paginatedRequest->limit,
            total_pages: (int) ceil($total / $paginatedRequest->limit)
        );
    }
}
