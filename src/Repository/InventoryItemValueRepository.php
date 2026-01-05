<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CustomField;
use App\Entity\InventoryItem;
use App\Entity\InventoryItemValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий для управления значениями кастомных полей предметов инвентаря.
 *
 * @extends ServiceEntityRepository<InventoryItemValue>
 */
final class InventoryItemValueRepository extends ServiceEntityRepository
{
    /**
     * Создает новый экземпляр репозитория.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryItemValue::class);
    }

    /**
     * Устанавливает или обновляет значение поля для предмета.
     *
     * @param InventoryItem $item Предмет инвентаря.
     * @param CustomField $field Кастомное поле.
     * @param string|null $rawValue Новое значение.
     */
    public function setValue(
        InventoryItem $item,
        CustomField $field,
        ?string $rawValue
    ): void {
        $value = $this->findOneBy([
            'item' => $item,
            'field' => $field,
        ]);

        if (!$value) {
            $value = new InventoryItemValue();
            $value->setItem($item);
            $value->setField($field);
            $this->_em->persist($value);
        }

        $value->setValue($rawValue);
    }

    /**
     * Возвращает все значения полей для конкретного предмета.
     *
     * @param InventoryItem $item Предмет инвентаря.
     * @return InventoryItemValue[] Список значений.
     */
    public function findByItem(InventoryItem $item): array
    {
        return $this->findBy(['item' => $item]);
    }
}
