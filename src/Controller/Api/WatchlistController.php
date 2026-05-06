<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Infrastructure\Routing\RouteRequirements;
use App\Presentation\Http\Response\ApiResponse;
use App\Service\Watchlist\WatchlistInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/api/v1/watchlist', name: 'watchlist_')]
final class WatchlistController extends AbstractController
{
    public function __construct(
        private readonly WatchlistInterface $watchlist,
    )
    { }

    #[Route(path: '', name: 'list', methods: 'GET')]
    public function index(): JsonResponse
    {
        return $this->json([], Response::HTTP_OK);
    }

    #[Route(
        path: '/{symbol}',
        name: 'add',
        requirements: ['symbol' => RouteRequirements::SYMBOL_REGEX],
        methods: 'POST'
    )]
    public function addStock(#[CurrentUser] ?User $user, string $symbol): JsonResponse
    {
        $item = $this->watchlist->addItem($user, $symbol);

        return ApiResponse::success(data: $item, status: Response::HTTP_CREATED);
    }
}
