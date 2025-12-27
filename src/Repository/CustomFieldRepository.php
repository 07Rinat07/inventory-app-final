<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Enum\CustomFieldType;
use App\Entity\CustomField;
use App\Entity\Inventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class CustomFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomField::class);
    }

    /**
     * @return CustomField[]
     */
    public function findByInventoryOrdered(Inventory $inventory): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.inventory = :inv')
            ->setParameter('inv', $inventory)
            ->orderBy('f.position', 'ASC')
            ->addOrderBy('f.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByInventoryAndType(
        Inventory $inventory,
        CustomFieldType $type
    ): int {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.inventory = :inv')
            ->andWhere('f.type = :type')
            ->setParameter('inv', $inventory)
            ->setParameter('type', $type->value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getNextPosition(Inventory $inventory): int
    {
        $max = $this->createQueryBuilder('f')
            ->select('COALESCE(MAX(f.position), -1)')
            ->andWhere('f.inventory = :inv')
            ->setParameter('inv', $inventory)
            ->getQuery()
            ->getSingleScalarResult();

        return ((int) $max) + 1;
    }

    /**
     * Bulk delete (toolbar action)
     *
     * @param int[] $ids
     */
    public function deleteByIds(Inventory $inventory, array $ids): int
    {
        if ($ids === []) {
            return 0;
        }

        return $this->createQueryBuilder('f')
            ->delete()
            ->andWhere('f.inventory = :inv')
            ->andWhere('f.id IN (:ids)')
            ->setParameter('inv', $inventory)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
