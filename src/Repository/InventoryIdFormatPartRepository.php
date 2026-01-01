<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\InventoryIdFormatPart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InventoryIdFormatPart>
 */
final class InventoryIdFormatPartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryIdFormatPart::class);
    }

    /**
     * Возвращает части формата ID в правильном порядке.
     *
     * Используется:
     * - UI формата
     * - генерация ID
     * - возможный API
     *
     * @return InventoryIdFormatPart[]
     */
    public function findOrderedByInventory(Inventory $inventory): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.inventory = :inventory')
            ->setParameter('inventory', $inventory)
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.id', 'ASC') // детерминизм, если позиции совпали в истории
            ->getQuery()
            ->getResult();
    }

    /**
     * Удаляет все части формата для инвентаря одним SQL DELETE.
     * Используется перед сохранением новых частей формата.
     * - query->execute() выполняет DELETE сразу в БД
     * - это решает конфликт UNIQUE при “replace” (inventory_id, position)
     */
    public function deleteByInventory(Inventory $inventory): int
    {
        return $this->createQueryBuilder('p')
            ->delete()
            ->andWhere('p.inventory = :inventory')
            ->setParameter('inventory', $inventory)
            ->getQuery()
            ->execute();
    }
}
