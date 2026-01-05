<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryItemValueRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Представляет значение кастомного поля для конкретного предмета инвентаря.
 */
#[ORM\Entity(repositoryClass: InventoryItemValueRepository::class)]
#[ORM\Table(name: 'inventory_item_values')]
#[ORM\UniqueConstraint(name: 'uniq_item_field', columns: ['item_id', 'field_id'])]
class InventoryItemValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Предмет инвентаря, к которому относится значение.
     */
    #[ORM\ManyToOne(targetEntity: InventoryItem::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private InventoryItem $item;

    /**
     * Кастомное поле, для которого задано значение.
     */
    #[ORM\ManyToOne(targetEntity: CustomField::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private CustomField $field;

    /**
     * Строковое представление значения поля.
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $value = null;

    // -------- getters / setters --------

    /**
     * Идентификатор значения.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Возвращает предмет инвентаря.
     */
    public function getItem(): InventoryItem
    {
        return $this->item;
    }

    /**
     * Устанавливает предмет инвентаря.
     */
    public function setItem(InventoryItem $item): self
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Возвращает кастомное поле.
     */
    public function getField(): CustomField
    {
        return $this->field;
    }

    /**
     * Устанавливает кастомное поле.
     */
    public function setField(CustomField $field): self
    {
        $this->field = $field;
        return $this;
    }

    /**
     * Возвращает значение.
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Устанавливает значение.
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;
        return $this;
    }
}
