<?php

declare(strict_types=1);

namespace App\Service\Watchlist;

use App\Entity\User;
use App\Entity\WatchlistItem;
use App\Presentation\Http\Exception\Services\StockExistingInWatchlistException;
use App\Presentation\Http\Exception\Services\StockNotFound;
use App\Presentation\Http\Exception\Services\WatchlistItemNotFound;
use App\Repository\WatchlistItemRepository;
use App\Service\Stock\StockService;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Cache\InvalidArgumentException;

final readonly class WatchlistService implements WatchlistInterface
{
    public function __construct(
        private StockService            $stockService,
        private EntityManagerInterface  $entityManager,
        private WatchlistItemRepository $watchlistItemRepository,
    )
    { }

    public function getItems(User $user): ?array
    {
        $items = $this->watchlistItemRepository->findUserWatchlistItems($user);

        if (empty($items)) {
            return null;
        }

        return $items;
    }

    /**
     * @param User $user
     * @param string $symbol
     * @return WatchlistItem
     * @throws InvalidArgumentException
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
