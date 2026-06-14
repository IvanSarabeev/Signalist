<?php

declare(strict_types=1);

namespace App\Service\Watchlist;

use App\Entity\User;
use App\Entity\WatchlistItem;
use App\Presentation\Http\Exception\Services\StockExistingInWatchlistException;
use App\Presentation\Http\Exception\Services\StockNotFound;
use App\Presentation\Http\Exception\Services\WatchlistItemNotFound;
use App\Presentation\Http\Request\PaginatedRequest;
use App\Presentation\Http\Response\PaginatedResponse;
use App\Repository\WatchlistItemRepository;
use App\Service\Stock\StockServiceInterface;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

final readonly class WatchlistServiceService implements WatchlistServiceInterface
{
    public function __construct(
        private StockServiceInterface   $stockService,
        private EntityManagerInterface  $entityManager,
        private WatchlistItemRepository $watchlistItemRepository,
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
        $total = $this->watchlistItemRepository->countUserWatchlistItems($user);

        if ($total === 0) {
            return null;
        }

        $items = $this->watchlistItemRepository->findUserWatchlistItems(
            $user,
            $pagination->limit,
            $pagination->getOffset()
        );

        return new PaginatedResponse(
            items:       $items,
            total:       $total,
            page:        $pagination->page,
            limit:       $pagination->limit,
            total_pages: (int) ceil($total / $pagination->limit),
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

        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }
}
