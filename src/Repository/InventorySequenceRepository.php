<?php

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\InventorySequence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InventorySequenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventorySequence::class);
    }

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
