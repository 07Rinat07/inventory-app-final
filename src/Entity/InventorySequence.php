<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventorySequenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventorySequenceRepository::class)]
#[ORM\Table(name: 'inventory_sequence')]
#[ORM\UniqueConstraint(name: 'uniq_inventory_sequence', columns: ['inventory_id'])]
class InventorySequence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Inventory::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    #[ORM\Column(nullable: false)]
    private int $nextValue = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    public function setInventory(Inventory $inventory): self
    {
        $this->inventory = $inventory;
        return $this;
    }

    /**
     * Returns current value and increments the sequence.
     * IMPORTANT: Must be used inside a DB transaction with row-level lock.
     */
    public function next(): int
    {
        return $this->nextValue++;
    }

    public function getNextValue(): int
    {
        return $this->nextValue;
    }
}
