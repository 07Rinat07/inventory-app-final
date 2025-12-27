<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\InventorySequence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class InventorySequenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventorySequence::class);
    }

    /**
     * Возвращает sequence для inventory с блокировкой строки (SELECT FOR UPDATE).
     *
     * ВАЖНО:
     * - метод ДОЛЖЕН вызываться ТОЛЬКО внутри транзакции
     * - используется для генерации кастомных ID
     */
    public function findOneByInventoryForUpdate(
        Inventory $inventory
    ): ?InventorySequence {
        $conn = $this->getEntityManager()->getConnection();

        $sql = <<<SQL
SELECT id
FROM inventory_sequence
WHERE inventory_id = :inventoryId
FOR UPDATE
SQL;

        $id = $conn->fetchOne($sql, [
            'inventoryId' => $inventory->getId(),
        ]);

        if ($id === false) {
            return null;
        }

        return $this->find((int) $id);
    }
}
