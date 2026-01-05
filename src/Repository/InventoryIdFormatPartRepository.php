<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\InventoryIdFormatPart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий для работы с частями формата кастомного идентификатора.
 *
 * @extends ServiceEntityRepository<InventoryIdFormatPart>
 */
final class InventoryIdFormatPartRepository extends ServiceEntityRepository
{
    /**
     * Создает новый экземпляр репозитория.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryIdFormatPart::class);
    }

    /**
     * Возвращает части формата идентификатора в правильном порядке.
     *
     * @param Inventory $inventory Инвентарь.
     * @return InventoryIdFormatPart[] Список частей формата.
     */
    public function findOrderedByInventory(Inventory $inventory): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.inventory = :inventory')
            ->setParameter('inventory', $inventory)
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.id', 'ASC') // детерминизм, если позиции совпали
            ->getQuery()
            ->getResult();
    }

    /**
     * Удаляет все части формата для инвентаря.
     * Используется перед сохранением новых частей формата для избежания конфликтов UNIQUE.
     *
     * @param Inventory $inventory Инвентарь.
     * @return int Количество удаленных записей.
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
