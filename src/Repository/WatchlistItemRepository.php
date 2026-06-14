<?php

namespace App\Repository;

use App\Entity\Stock;
use App\Entity\User;
use App\Entity\WatchlistItem;
use App\Presentation\Http\Response\WatchlistItem\WatchlistItemResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\AbstractQuery;
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

    public function findUserWatchlistItems(User $user, int $limit, int $offset): array
    {
        $items = $this->createQueryBuilder('wi')
            ->innerJoin('wi.stock', 's')
            ->addSelect('s')
            ->andWhere('wi.user = :user')
            ->setParameter('user', $user)
            ->orderBy('wi.addedAt', Order::Descending->value)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);

        if (empty($items)) {
            return [];
        }

        return array_map(
            fn(array $item) => new WatchlistItemResponse(
                id:             $item['id'],
                symbol:         $item['stock']['symbol'],
                name:           $item['stock']['name'],
                exchange:       $item['stock']['exchange'],
                currency:       $item['stock']['currency'],
                price:          $item['stock']['cachedPrice'],
                change_percent: $item['stock']['cachedChangePercent'],
                market_cap:     $item['stock']['cachedHigh'],
                pe_ratio:       $item['stock']['cachedLow'],
                added_at:       $item['addedAt']->format('Y-m-d H:i:s'),
                sort_order:     $item['sortOrder'],
            ),
            $items,
        );
    }

    public function countUserWatchlistItems(User $user): int
    {
        return (int) $this->createQueryBuilder('wi')
            ->select('COUNT(wi.id)')
            ->andWhere('wi.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
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
