<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryItemRepository::class)]
#[ORM\Table(name: 'inventory_items')]
#[ORM\UniqueConstraint(name: 'uniq_inventory_custom_id', columns: ['inventory_id', 'custom_id'])]
class InventoryItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Inventory::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    #[ORM\Column(name: 'custom_id', length: 255, nullable: false)]
    private string $customId;

    #[ORM\Version]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $version = 1;

    // -------- getters / setters --------

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

    public function getCustomId(): string
    {
        return $this->customId;
    }

    public function setCustomId(string $customId): self
    {
        $this->customId = $customId;
        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }
}
