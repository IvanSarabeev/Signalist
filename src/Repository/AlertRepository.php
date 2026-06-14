<?php

namespace App\Repository;

use App\Entity\Alert;
use App\Entity\User;
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
            return array_map(
                fn(array $item) => [

                ],
                $items
            );
        }
    }
}
