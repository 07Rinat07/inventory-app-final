<?php

namespace App\Entity;

use App\Repository\InventorySequenceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Представляет счетчик для генерации последовательных идентификаторов в инвентаре.
 */
#[ORM\Entity(repositoryClass: InventorySequenceRepository::class)]
#[ORM\Table(name: 'inventory_sequence')]
#[ORM\UniqueConstraint(name: 'uniq_inventory_sequence', columns: ['inventory_id'])]
class InventorySequence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Инвентарь, для которого ведется счетчик.
     */
    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    /**
     * Следующее значение последовательности.
     */
    #[ORM\Column(type: 'integer')]
    private int $nextValue = 1;

    /**
     * Возвращает следующее значение.
     */
    public function getNextValue(): int
    {
        return $this->nextValue;
    }

    /**
     * Увеличивает значение счетчика на 1.
     */
    public function increment(): void
    {
        ++$this->nextValue;
    }

    /**
     * Устанавливает инвентарь.
     */
    public function setInventory(Inventory $inventory): void
    {
        $this->inventory = $inventory;
    }
}
