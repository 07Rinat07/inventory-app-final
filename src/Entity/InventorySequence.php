<?php

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

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    #[ORM\Column(type: 'integer')]
    private int $nextValue = 1;

    public function getNextValue(): int
    {
        return $this->nextValue;
    }

    public function increment(): void
    {
        ++$this->nextValue;
    }

    public function setInventory(Inventory $inventory): void
    {
        $this->inventory = $inventory;
    }
}
