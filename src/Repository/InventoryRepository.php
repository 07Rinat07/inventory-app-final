<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий для управления сущностями инвентаря.
 *
 * @extends ServiceEntityRepository<Inventory>
 */
final class InventoryRepository extends ServiceEntityRepository
{
    /**
     * Создает новый экземпляр репозитория.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    /**
     * Показываем инвентари, которые доступны юзеру.
     * Админ видит вообще всё, обычный юзер — своё + то, что помечено как public.
     */
    public function findAvailableForUser(User $user): array
    {
        // Админам можно всё
        if (\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->createQueryBuilder('i')
                ->orderBy('i.id', 'DESC')
                ->getQuery()
                ->getResult();
        }

        // Остальным — только их личное или публичное
        return $this->createQueryBuilder('i')
            ->andWhere('i.owner = :user OR i.isPublic = true')
            ->setParameter('user', $user)
            ->orderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Сохраняем (persist) инвентарь. Если flush = true, то сразу пишем в базу.
     */
    public function save(Inventory $inventory, bool $flush = false): void
    {
        $this->getEntityManager()->persist($inventory);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Удаляем инвентарь.
     */
    public function remove(Inventory $inventory, bool $flush = false): void
    {
        $this->getEntityManager()->remove($inventory);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
