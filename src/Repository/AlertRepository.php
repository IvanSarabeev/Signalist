<?php

namespace App\Repository;

use App\Entity\Alert;
use App\Entity\User;
use App\Presentation\Http\Response\Alerts\AlertItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Alert>
 */
class AlertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alert::class);
    }

    public function countUserAlerts(User $user): int
    {
        return (int) $this->createQueryBuilder('alert')
            ->select('COUNT(alert.id)')
            ->andWhere('alert.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findUserAlertItems(User $user, int $limit, int $offset): array
    {
        $items = $this->createQueryBuilder('alert')
            ->leftJoin('alert.stock', 'stock')
            ->addSelect('stock')
            ->andWhere('alert.user = :user')
            ->setParameter('user', $user)
            ->orderBy('alert.createdAt', Order::Descending->value)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);

        if (empty($items)) {
            return [];
        }

        return array_map(
            fn(array $item) => new AlertItems(
                id:                $item['id'],
                alert_name:        $item['alertName'],
                alert_type_label:  $item['alertType']->label(),
                condition_quality: $item['conditionQuality']->value,
                condition_label:   $item['conditionQuality']->label(),
                condition_symbol:  $item['conditionQuality']->symbol(),
                threshold_value:   $item['thresholdValue'],
                frequency:         $item['frequency']->value,
                frequency_label:   $item['frequency']->label(),
                is_active:         $item['isActive'],
                created_at:        $item['createdAt']?->format('Y-m-d H:i:s'),
                last_triggered_at: $item['lastTriggeredAt']?->format('Y-m-d H:i:s'),
                stock_symbol:      $item['stock']['symbol'] ?? null,
                name:              $item['stock']['name'] ?? null,
                price:             $item['stock']['cachedPrice'] ?? 0,
                currency:          $item['stock']['currency'],
                market_cap:        $item['stock']['cachedHigh'],
                logo_url:          $item['stock']['logoUrl'],
                change_percent:    $item['stock']['cachedChangePercent'],
            ),
            $items
        );
    }
}
