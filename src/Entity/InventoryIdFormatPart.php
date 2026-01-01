<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\ValueObject\InventoryIdPartType;
use App\Repository\InventoryIdFormatPartRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryIdFormatPartRepository::class)]
#[ORM\Table(name: 'inventory_id_format_part')]
#[ORM\UniqueConstraint(name: 'uniq_inventory_format_position', columns: ['inventory_id', 'position'])]
class InventoryIdFormatPart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Inventory::class, inversedBy: 'idFormatParts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    /**
     * enumType хранит значение enum в БД.
     */
    #[ORM\Column(enumType: InventoryIdPartType::class)]
    private InventoryIdPartType $type;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $param1 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $param2 = null;

    // ---------------- getters / setters ----------------

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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function getType(): InventoryIdPartType
    {
        return $this->type;
    }

    public function setType(InventoryIdPartType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getParam1(): ?string
    {
        return $this->param1;
    }

    public function setParam1(?string $param1): self
    {
        $this->param1 = $param1 !== null ? trim($param1) : null;
        if ($this->param1 === '') {
            $this->param1 = null;
        }
        return $this;
    }

    public function getParam2(): ?string
    {
        return $this->param2;
    }

    public function setParam2(?string $param2): self
    {
        $this->param2 = $param2 !== null ? trim($param2) : null;
        if ($this->param2 === '') {
            $this->param2 = null;
        }
        return $this;
    }
}
