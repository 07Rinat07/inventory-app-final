<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryItemValueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryItemValueRepository::class)]
#[ORM\Table(
    name: 'inventory_item_values',
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'uniq_item_field',
            columns: ['inventory_item_id', 'custom_field_id']
        )
    ]
)]
class InventoryItemValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * К какому item относится значение
     */
    #[ORM\ManyToOne(targetEntity: InventoryItem::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private InventoryItem $inventoryItem;

    /**
     * Для какого custom field
     */
    #[ORM\ManyToOne(targetEntity: CustomField::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private CustomField $customField;

    /**
     * Значение (храним строкой, тип определяет CustomFieldType)
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $value = null;

    // ---------------- getters / setters ----------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInventoryItem(): InventoryItem
    {
        return $this->inventoryItem;
    }

    public function setInventoryItem(InventoryItem $item): self
    {
        $this->inventoryItem = $item;
        return $this;
    }

    public function getCustomField(): CustomField
    {
        return $this->customField;
    }

    public function setCustomField(CustomField $field): self
    {
        $this->customField = $field;
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
