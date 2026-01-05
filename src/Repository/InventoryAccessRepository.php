<?php

namespace App\Repository;

use App\Entity\InventoryAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий для управления правами доступа (ACL) к инвентарям.
 *
 * @extends ServiceEntityRepository<InventoryAccess>
 */
class InventoryAccessRepository extends ServiceEntityRepository
{
    /**
     * Создает новый экземпляр репозитория.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryAccess::class);
    }
}
