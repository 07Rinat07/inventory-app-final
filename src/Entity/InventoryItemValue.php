<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryItemValueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryItemValueRepository::class)]
#[ORM\Table(name: 'inventory_item_values')]
#[ORM\UniqueConstraint(name: 'uniq_item_field', columns: ['item_id', 'field_id'])]
class InventoryItemValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: InventoryItem::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private InventoryItem $item;

    #[ORM\ManyToOne(targetEntity: CustomField::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private CustomField $field;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $value = null;

    // -------- getters / setters --------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItem(): InventoryItem
    {
        return $this->item;
    }

    public function setItem(InventoryItem $item): self
    {
        $this->item = $item;
        return $this;
    }

    public function getField(): CustomField
    {
        return $this->field;
    }

    public function setField(CustomField $field): self
    {
        $this->field = $field;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;
        return $this;
    }
}
