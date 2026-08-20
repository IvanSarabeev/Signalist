<?php

declare(strict_types=1);

namespace App\Service\Watchlist;

use App\Entity\User;
use App\Entity\WatchlistItem;
use App\Presentation\Http\Exception\Services\StockExistingInWatchlistException;
use App\Presentation\Http\Exception\Services\StockNotFound;
use App\Presentation\Http\Exception\Services\Watchlist\WatchlistItemDeletionException;
use App\Presentation\Http\Exception\Services\Watchlist\WatchlistItemNotFound;
use App\Presentation\Http\Request\PaginatedRequest;
use App\Presentation\Http\Response\PaginatedResponse;
use App\Repository\WatchlistItemRepository;
use App\Service\Cache\CacheManagerInterface;
use App\Service\Cache\CacheProfile;
use App\Service\Cache\CacheTag;
use App\Service\Stock\StockServiceInterface;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Throwable;

final readonly class WatchlistService implements WatchlistServiceInterface
{
    public function __construct(
        private StockServiceInterface   $stockService,
        private EntityManagerInterface  $entityManager,
        private WatchlistItemRepository $watchlistItemRepository,
        private CacheManagerInterface   $cacheManager,
    )
    { }

    /**
     * Get the watchlist items that a user has and return pagination based DTO response
     *
     * @param User $user
     * @param PaginatedRequest $pagination
     * @return PaginatedResponse|null
     */
    public function getItems(User $user, PaginatedRequest $pagination): ?PaginatedResponse
    {
        $cachePayload = $this->cacheManager->get(
            CacheProfile::USER_WATCHLIST,
            [$user->getId(), $pagination->page, $pagination->limit],
            function () use ($user, $pagination): array {
                $total = $this->watchlistItemRepository->countUserWatchlistItems($user);

                return [
                    'total' => $total,
                    'items' => $total === 0
                        ? []
                        : $this->watchlistItemRepository->findUserWatchlistItems(
                            $user,
                            $pagination->limit,
                            $pagination->getOffset()
                        ),
                ];
            },
            [CacheTag::watchlist($user)],
        );

        if ($cachePayload['total'] === 0) {
            return null;
        }

        return new PaginatedResponse(
            items:       $cachePayload['items'],
            total:       $cachePayload['total'],
            page:        $pagination->page,
            limit:       $pagination->limit,
            total_pages: (int) ceil($cachePayload['total'] / $pagination->limit),
        );
    }

    /**
     * @param User $user
     * @param string $symbol
     * @return WatchlistItem
     * @throws Exception
     */
    public function addItem(User $user, string $symbol): WatchlistItem
    {
        $stock = $this->stockService->findOrCreateFromFinnhubStock($symbol);

        $existingItem = $this->watchlistItemRepository->findOneBy([
            'user' => $user,
            'stock' => $stock
        ]);

        if ($existingItem) {
            throw new StockExistingInWatchlistException();
        }

        $item = new WatchlistItem();
        $item->setUser($user);
        $item->setStock($stock);
        $item->setAddedAt(new DateTimeImmutable(timezone: new DateTimeZone('Europe/Sofia')));

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        $this->cacheManager->invalidate(CacheTag::watchlist($user));

        return $item;
    }

    /**
     * Delete a specific watchlist item for a user by stack symbol
     *
     * @param User $user
     * @param string $symbol
     * @return void
     *
     * @throws StockNotFound
     * @throws WatchlistItemNotFound
     */
    public function deleteItem(User $user, string $symbol): void
    {
        $stock = $this->stockService->findStockBySymbol($symbol);

        if (!$stock) {
            throw new StockNotFound();
        }

        $item = $this->watchlistItemRepository->findUserWatchlistItem($user, $stock);

        if (!$item) {
            throw new WatchlistItemNotFound();
        }

        try {
            $this->entityManager->remove($item);
            $this->entityManager->flush();
        } catch (Throwable) {
            throw new WatchlistItemDeletionException();
        }

        $this->cacheManager->invalidate(CacheTag::watchlist($user));
    }
}
