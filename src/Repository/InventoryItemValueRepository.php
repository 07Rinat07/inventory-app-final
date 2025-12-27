<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CustomField;
use App\Entity\InventoryItem;
use App\Entity\InventoryItemValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class InventoryItemValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryItemValue::class);
    }

    /**
     * @return InventoryItemValue[]
     */
    public function findByItem(InventoryItem $item): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.inventoryItem = :item')
            ->setParameter('item', $item)
            ->getQuery()
            ->getResult();
    }

    public function findOneByItemAndField(
        InventoryItem $item,
        CustomField $field
    ): ?InventoryItemValue {
        return $this->findOneBy([
            'inventoryItem' => $item,
            'customField' => $field,
        ]);
    }

    /**
     * Удобный метод для записи значения
     */
    public function setValue(
        InventoryItem $item,
        CustomField $field,
        ?string $value
    ): InventoryItemValue {
        $entity = $this->findOneByItemAndField($item, $field);

        if (!$entity) {
            $entity = new InventoryItemValue();
            $entity
                ->setInventoryItem($item)
                ->setCustomField($field);
            $this->_em->persist($entity);
        }

        $entity->setValue($value);

        return $entity;
    }
}
