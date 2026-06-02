<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Api;

use App\Entity\User;
use App\Infrastructure\Routing\RouteRequirements;
use App\Presentation\Http\Response\ApiResponse;
use App\Service\Watchlist\WatchlistInterface;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Throwable;

#[Route(path: '/api/v1/watchlist', name: 'watchlist_')]
#[OA\Tag(name: 'Watchlist')]
final class WatchlistController extends AbstractController
{
    private const WATCHLIST_PREFIX = 'Watchlist: ';

    public function __construct(
        private readonly WatchlistInterface $watchlist,
        private readonly LoggerInterface    $logger,
    )
    { }

    #[Route(path: '', name: 'list', methods: ['GET'])]
    public function index(#[CurrentUser] ?User $user): JsonResponse
    {
        $items = $this->watchlist->getItems($user);

        if ($items === null) {
            return ApiResponse::success(status: Response::HTTP_NO_CONTENT);
        }

        return ApiResponse::success($items);
    }

    #[Route(
        path: '/{symbol}',
        name: 'add',
        requirements: ['symbol' => RouteRequirements::SYMBOL_REGEX],
        methods: ['POST']
    )]
    public function addStock(#[CurrentUser] ?User $user, string $symbol): JsonResponse
    {
        $item = $this->watchlist->addItem($user, $symbol);

        return ApiResponse::success(data: $item->toArray(), status: Response::HTTP_CREATED);
    }

    #[Route(
        path: '/{symbol}',
        name: 'delete',
        requirements: ['symbol' => RouteRequirements::SYMBOL_REGEX],
        methods: ['DELETE']
    )]
    public function deleteStock(#[CurrentUser] ?User $user, string $symbol): JsonResponse
    {
        try {
            $this->watchlist->deleteItem($user, $symbol);

            return ApiResponse::success(status: Response::HTTP_NO_CONTENT);
        } catch (Throwable $exception) {
            $this->logger->warning(sprintf(
                self::WATCHLIST_PREFIX . 'Unable to delete stock: %s',
                $symbol
            ), ['message' => $exception->getMessage()]);

            return ApiResponse::error('Unable to remove ' . $symbol . ' from watchlist', status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
