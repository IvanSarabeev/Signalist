<?php

declare(strict_types=1);

namespace App\Service\Watchlist;

use App\Entity\User;
use App\Entity\WatchlistItem;
use App\Presentation\Http\Exception\Services\StockExistingInWatchlistException;
use App\Presentation\Http\Exception\Services\WatchlistItemNotFound;
use App\Repository\WatchlistItemRepository;
use App\Service\Stock\StockService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
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
        $item->setAddedAt(new DateTimeImmutable());

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $item;
    }

    /**
     * Delete a specific watchlist item from the stack
     * @param string $symbol
     * @return void
     */
    public function deleteItem(string $symbol): void
    {
        $item = $this->watchlistItemRepository->findOneBy(['stock' => $symbol]);

        if (!$item) {
            throw new WatchlistItemNotFound();
        }

        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }
}
