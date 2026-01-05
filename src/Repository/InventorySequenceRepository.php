<?php

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\InventorySequence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий для работы со счетчиками (последовательностями) инвентарей.
 *
 * @extends ServiceEntityRepository<InventorySequence>
 */
class InventorySequenceRepository extends ServiceEntityRepository
{
    /**
     * Создает новый экземпляр репозитория.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventorySequence::class);
    }

    /**
     * Ищет последовательность для инвентаря с использованием пессимистической блокировки (FOR UPDATE).
     * Это гарантирует атомарность получения и инкремента значения в многопоточной среде.
     *
     * @param Inventory $inventory Инвентарь.
     * @return InventorySequence|null Объект последовательности.
     */
    public function findForUpdate(Inventory $inventory): ?InventorySequence
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.inventory = :inventory')
            ->setParameter('inventory', $inventory)
            ->getQuery()
            ->setLockMode(\Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE)
            ->getOneOrNullResult();
    }
}
