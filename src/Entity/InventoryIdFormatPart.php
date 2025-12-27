<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryIdFormatPartRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryIdFormatPartRepository::class)]
#[ORM\Table(name: 'inventory_id_format_part')]
#[ORM\UniqueConstraint(
    name: 'uniq_inventory_format_position',
    columns: ['inventory_id', 'position']
)]
class InventoryIdFormatPart
{
    public const TYPE_FIXED    = 'FIXED';
    public const TYPE_RANDOM   = 'RANDOM';
    public const TYPE_GUID     = 'GUID';
    public const TYPE_DATETIME = 'DATETIME';
    public const TYPE_SEQ      = 'SEQ';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Inventory::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    #[ORM\Column(nullable: false)]
    private int $position;

    #[ORM\Column(length: 10, nullable: false)]
    private string $type;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $param1 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $param2 = null;

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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (!\in_array($type, [
            self::TYPE_FIXED,
            self::TYPE_RANDOM,
            self::TYPE_GUID,
            self::TYPE_DATETIME,
            self::TYPE_SEQ,
        ], true)) {
            throw new \InvalidArgumentException('Invalid ID format part type');
        }

        $this->type = $type;
        return $this;
    }

    public function getParam1(): ?string
    {
        return $this->param1;
    }

    public function setParam1(?string $param1): self
    {
        $this->param1 = $param1;
        return $this;
    }

    public function getParam2(): ?string
    {
        return $this->param2;
    }

    public function setParam2(?string $param2): self
    {
        $this->param2 = $param2;
        return $this;
    }
}
