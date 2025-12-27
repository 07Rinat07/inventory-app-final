<?php

namespace App\Entity;

use App\Domain\ValueObject\InventoryIdPartType;
use App\Repository\InventoryIdFormatPartRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryIdFormatPartRepository::class)]
#[ORM\Table(
    name: 'inventory_id_format_part',
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'uniq_inventory_format_position',
            columns: ['inventory_id', 'position']
        )
    ]
)]
class InventoryIdFormatPart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'idFormatParts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    #[ORM\Column(type: 'integer')]
    private int $position;

    #[ORM\Column(enumType: InventoryIdPartType::class)]
    private InventoryIdPartType $type;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $param1 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $param2 = null;

    // --- getters / setters ---

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getType(): InventoryIdPartType
    {
        return $this->type;
    }

    public function getParam1(): ?string
    {
        return $this->param1;
    }

    public function getParam2(): ?string
    {
        return $this->param2;
    }
}
