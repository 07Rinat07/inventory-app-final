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
     * @return InventoryItemValue[]
     */
    public function findByItem(InventoryItem $item): array
    {
        return $this->findBy(['item' => $item]);
    }
}
