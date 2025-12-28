<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\CustomField\CustomFieldType;
use App\Repository\CustomFieldRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomFieldRepository::class)]
#[ORM\Table(
    name: 'custom_fields',
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'uniq_inventory_position',
            columns: ['inventory_id', 'position']
        )
    ]
)]
class CustomField
{
    #[ORM\Id]
    #[ORM\GeneratedValue] // В Postgres нормально работает с IDENTITY/DEFAULT
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Inventory::class, inversedBy: 'customFields')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    #[ORM\Column(type: 'string', length: 20)]
    private string $type;

    #[ORM\Column(type: 'integer')]
    private int $position;

    #[ORM\Column(type: 'boolean')]
    private bool $isRequired = false;

    public function __construct(Inventory $inventory, CustomFieldType $type, int $position)
    {
        $this->inventory = $inventory;
        $this->type = $type->value;
        $this->position = $position;
    }

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

    public function getType(): CustomFieldType
    {
        return CustomFieldType::from($this->type);
    }

    public function setType(CustomFieldType $type): self
    {
        $this->type = $type->value;
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

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $required): self
    {
        $this->isRequired = $required;
        return $this;
    }
}
