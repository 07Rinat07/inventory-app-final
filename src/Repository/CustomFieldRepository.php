<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\CustomField\CustomFieldType;
use App\Entity\CustomField;
use App\Entity\Inventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий для управления кастомными полями инвентаря.
 */
final class CustomFieldRepository extends ServiceEntityRepository
{
    /** Максимальное количество полей одного типа в рамках одного инвентаря. */
    public const TYPE_LIMIT_PER_INVENTORY = 3;

    /**
     * Создает новый экземпляр репозитория.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomField::class);
    }

    /**
     * Возвращает поля инвентаря в правильном порядке.
     *
     * @param Inventory $inventory Инвентарь.
     * @return CustomField[] Список полей.
     */
    public function findByInventoryOrdered(Inventory $inventory): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.inventory = :inv')
            ->setParameter('inv', $inventory)
            ->orderBy('f.position', 'ASC')
            ->addOrderBy('f.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Подсчитывает общее количество полей у инвентаря.
     *
     * @param Inventory $inventory Инвентарь.
     * @return int Количество полей.
     */
    public function countByInventory(Inventory $inventory): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.inventory = :inv')
            ->setParameter('inv', $inventory)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Подсчитывает количество полей конкретного типа у инвентаря.
     * Полезно для проверки лимита (до 3 полей каждого типа).
     *
     * @param Inventory $inventory Инвентарь.
     * @param CustomFieldType $type Тип поля.
     * @return int Количество полей данного типа.
     */
    public function countByInventoryAndType(Inventory $inventory, CustomFieldType $type): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.inventory = :inv')
            ->andWhere('f.type = :type')
            ->setParameter('inv', $inventory)
            ->setParameter('type', $type->value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Проверяет, достигнут ли лимит полей конкретного типа (не больше 3).
     *
     * @param Inventory $inventory Инвентарь.
     * @param CustomFieldType $type Тип поля.
     * @return bool True, если лимит достигнут.
     */
    public function hasReachedTypeLimit(Inventory $inventory, CustomFieldType $type): bool
    {
        return $this->countByInventoryAndType($inventory, $type) >= self::TYPE_LIMIT_PER_INVENTORY;
    }

    /**
     * Возвращает следующую позицию для нового поля (max + 1).
     *
     * @param Inventory $inventory Инвентарь.
     * @return int Следующая свободная позиция.
     */
    public function getNextPosition(Inventory $inventory): int
    {
        $max = $this->createQueryBuilder('f')
            ->select('COALESCE(MAX(f.position), -1)')
            ->andWhere('f.inventory = :inv')
            ->setParameter('inv', $inventory)
            ->getQuery()
            ->getSingleScalarResult();

        return ((int) $max) + 1;
    }

    /**
     * Сохраняет поле в базе данных.
     *
     * @param CustomField $field Объект поля.
     * @param bool $flush Нужно ли сразу выполнить flush.
     */
    public function save(CustomField $field, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->persist($field);

        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Удаляет поле из базы данных.
     *
     * @param CustomField $field Объект поля.
     * @param bool $flush Нужно ли сразу выполнить flush.
     */
    public function remove(CustomField $field, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->remove($field);

        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Массовое удаление полей по списку ID.
     *
     * @param Inventory $inventory Инвентарь.
     * @param int[] $ids Список идентификаторов полей.
     * @return int Количество удаленных записей.
     */
    public function deleteByIds(Inventory $inventory, array $ids): int
    {
        if ($ids === []) {
            return 0;
        }

        return $this->createQueryBuilder('f')
            ->delete()
            ->andWhere('f.inventory = :inv')
            ->andWhere('f.id IN (:ids)')
            ->setParameter('inv', $inventory)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
