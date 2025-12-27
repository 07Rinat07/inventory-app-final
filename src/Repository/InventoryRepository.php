<?php

namespace App\Repository;

use App\Entity\Inventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inventory>
 */
class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    public function findAvailableForUser(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.accesses', 'a')
            ->andWhere('i.owner = :user OR i.isPublic = true OR a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function save(Inventory $inventory): void
    {
        $this->_em->persist($inventory);
        $this->_em->flush();
    }

}
