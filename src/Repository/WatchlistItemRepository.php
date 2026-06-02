<?php

namespace App\Repository;

use App\Entity\Stock;
use App\Entity\User;
use App\Entity\WatchlistItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WatchlistItem>
 */
class WatchlistItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WatchlistItem::class);
    }

    public function findUserWatchlistItems(User $user, $limit = 10): array
    {
        return $this->createQueryBuilder('wi')
            ->leftJoin('wi.stock', 's')
            ->addSelect('s')
            ->andWhere('wi.user = :user')
            ->setParameter('user', $user)
            ->orderBy('wi.addedAt', Order::Descending->value)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Finds a specific watchlist item by user and stock.
     *
     * @param User $user
     * @param Stock $stock
     * @return WatchlistItem|null
     */
    public function findUserWatchlistItem(User $user, Stock $stock): ?WatchlistItem
    {
        return $this->createQueryBuilder('wi')
            ->andWhere('wi.user = :user')
            ->setParameter('user', $user)
            ->andWhere('wi.stock = :stock')
            ->setParameter('stock', $stock)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
