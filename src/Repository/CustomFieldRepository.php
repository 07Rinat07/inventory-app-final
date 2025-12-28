<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\CustomField\CustomFieldType;
use App\Entity\CustomField;
use App\Entity\Inventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class CustomFieldRepository extends ServiceEntityRepository
{
    public const TYPE_LIMIT_PER_INVENTORY = 3;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomField::class);
    }

    /**
     * Поля инвентаря в правильном порядке.
     *
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

    /**
     * Сколько полей всего у инвентаря.
     */
    public function countByInventory(Inventory $inventory): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.inventory = :inv')
            ->setParameter('inv', $inventory)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Сколько полей конкретного типа уже есть у inventory.
     * Полезно под лимиты “до 3 полей каждого типа”.
     */
    public function countByInventoryAndType(Inventory $inventory, CustomFieldType $type): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.inventory = :inv')
            ->andWhere('f.type = :type')
            ->setParameter('inv', $inventory)
            ->setParameter('type', $type->value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Проверка лимита: “не больше 3 полей каждого типа на inventory”.
     */
    public function hasReachedTypeLimit(Inventory $inventory, CustomFieldType $type): bool
    {
        return $this->countByInventoryAndType($inventory, $type) >= self::TYPE_LIMIT_PER_INVENTORY;
    }

    /**
     * Следующая позиция: max(position) + 1.
     */
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

    public function save(CustomField $field, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->persist($field);

        if ($flush) {
            $em->flush();
        }
    }

    public function remove(CustomField $field, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->remove($field);

        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Bulk delete (на будущее под toolbar actions).
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
