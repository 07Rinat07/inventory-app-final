<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inventory>
 */
final class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    /**
     * Возвращает инвентари, доступные пользователю.
     *
     * Логика:
     * - ROLE_ADMIN:
     *     • видит ВСЕ инвентари (public + private, любые владельцы)
     * - ROLE_USER:
     *     • свои инвентари (public + private)
     *     • чужие ТОЛЬКО public
     *
     * Чужие private для ROLE_USER не возвращаются вообще.
     *
     * @return Inventory[]
     */
    public function findAvailableForUser(User $user): array
    {
        // Администратор видит всё
        if (\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->createQueryBuilder('i')
                ->orderBy('i.id', 'DESC')
                ->getQuery()
                ->getResult();
        }

        // Обычный пользователь: свои + public
        return $this->createQueryBuilder('i')
            ->andWhere('i.owner = :user OR i.isPublic = true')
            ->setParameter('user', $user)
            ->orderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Сохранение инвентаря
     */
    public function save(Inventory $inventory, bool $flush = false): void
    {
        $this->getEntityManager()->persist($inventory);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Удаление инвентаря
     */
    public function remove(Inventory $inventory, bool $flush = false): void
    {
        $this->getEntityManager()->remove($inventory);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
