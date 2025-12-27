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
     * Возвращает части формата ID в правильном порядке
     *
     * Используется:
     * - FORMAT_UI
     * - CustomIdGenerator
     * - API Provider
     */
    public function findOrderedByInventory(Inventory $inventory): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.inventory = :inventory')
            ->setParameter('inventory', $inventory)
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Удаляет все части формата для инвентаря
     *
     * Используется при reorder / save
     */
    public function deleteByInventory(Inventory $inventory): void
    {
        $this->createQueryBuilder('p')
            ->delete()
            ->andWhere('p.inventory = :inventory')
            ->setParameter('inventory', $inventory)
            ->getQuery()
            ->execute();
    }
}
