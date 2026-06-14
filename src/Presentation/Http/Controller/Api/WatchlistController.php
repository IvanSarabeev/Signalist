<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Api;

use App\Entity\User;
use App\Infrastructure\Routing\RouteRequirements;
use App\Presentation\Http\Request\PaginatedRequest;
use App\Presentation\Http\Response\ApiResponse;
use App\Service\Watchlist\WatchlistServiceInterface;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Throwable;

#[Route(path: '/api/v1/watchlist', name: 'watchlist_')]
#[OA\Tag(name: 'Watchlist')]
final class WatchlistController extends AbstractController
{
    public function __construct(
        private readonly WatchlistServiceInterface $watchlistService,
    )
    { }

    /**
     * Get the watchlist per a user
     *
     * @param User|null $user
     * @param Request $request
     * @return JsonResponse
     */
    #[Route(path: '', name: 'list', methods: ['GET'])]
    public function index(#[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        $pagination = PaginatedRequest::fromRequest($request);
        $items = $this->watchlistService->getItems($user, $pagination);

        if ($items === null) {
            return ApiResponse::success(status: Response::HTTP_NO_CONTENT);
        }

        return ApiResponse::success(data: $items->items, meta: [$items->toArray()]);
    }

    /**
     * Create an watchlist item per an user
     *
     * @param User|null $user
     * @param string $symbol
     * @return JsonResponse
     */
    #[Route(
        path: '/{symbol}',
        name: 'add',
        requirements: ['symbol' => RouteRequirements::SYMBOL_REGEX],
        methods: ['POST']
    )]
    public function addStock(#[CurrentUser] ?User $user, string $symbol): JsonResponse
    {
        $item = $this->watchlistService->addItem($user, $symbol);

        return ApiResponse::success(data: $item->toArray(), status: Response::HTTP_CREATED);
    }

    /**
     * Delete a specific watchlist item for a user
     *
     * @param User|null $user
     * @param string $symbol
     * @return JsonResponse
     */
    #[Route(
        path: '/{symbol}',
        name: 'delete',
        requirements: ['symbol' => RouteRequirements::SYMBOL_REGEX],
        methods: ['DELETE']
    )]
    public function deleteStock(#[CurrentUser] ?User $user, string $symbol): JsonResponse
    {
        $this->watchlistService->deleteItem($user, $symbol);

        return ApiResponse::success(status: Response::HTTP_NO_CONTENT);
    }
}
