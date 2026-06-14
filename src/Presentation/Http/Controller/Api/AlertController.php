<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Api;

use App\Entity\User;
use App\Presentation\Http\Request\Alert\CreateAlertRequest;
use App\Presentation\Http\Request\PaginatedRequest;
use App\Presentation\Http\Response\ApiResponse;
use App\Service\Alert\AlertServiceInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Tag(name: 'Alert')]
#[Route(path: '/api/v1/alerts', name: 'api_alerts_')]
final readonly class AlertController
{
    public function __construct(
        private AlertServiceInterface $alertService,
    )
    { }

    #[Route(path: '', name: 'list', methods: ['GET'])]
    public function index(#[CurrentUser] User $user, Request $request): JsonResponse
    {
        $pagination = PaginatedRequest::fromRequest($request);
        $alerts = $this->alertService->getAlerts($user, $pagination);

        if ($alerts === null) {
            return ApiResponse::success(status: Response::HTTP_NO_CONTENT);
        }

        return ApiResponse::success(data: $alerts->items, meta: [$alerts->toArray()]);
    }

    #[Route(path: '', name: 'create', methods: ['POST'])]
    public function create(#[CurrentUser] User $user, CreateAlertRequest $createAlertRequest): JsonResponse
    {
        $alert = $this->alertService->createAlert($user, $createAlertRequest);

        return ApiResponse::success(data: $alert->toArray(), status: Response::HTTP_CREATED);
    }
}
