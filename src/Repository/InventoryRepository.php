<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\InventoryAccess;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    /**
     * Инвентари, доступные пользователю:
     *  - свои (owner)
     *  - публичные
     *  - по ACL (есть запись InventoryAccess для пользователя)
     *
     * @return Inventory[]
     */
    public function findAvailableForUser(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->select('DISTINCT i') // защита от дублей при join
            ->leftJoin(
                InventoryAccess::class,
                'a',
                'WITH',
                'a.inventory = i AND a.user = :user'
            )
            ->andWhere('i.owner = :user OR i.isPublic = true OR a.id IS NOT NULL')
            ->setParameter('user', $user)
            ->orderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Сохранение сущности (унифицированный метод, чтобы сервисы не трогали EntityManager напрямую).
     */
    public function save(Inventory $inventory, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->persist($inventory);

        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Удаление сущности.
     */
    public function remove(Inventory $inventory, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->remove($inventory);

        if ($flush) {
            $em->flush();
        }
    }
}
