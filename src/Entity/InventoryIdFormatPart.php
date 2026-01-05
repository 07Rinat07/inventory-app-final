<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\ValueObject\InventoryIdPartType;
use App\Repository\InventoryIdFormatPartRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Представляет часть формата идентификатора инвентаря.
 */
#[ORM\Entity(repositoryClass: InventoryIdFormatPartRepository::class)]
#[ORM\Table(name: 'inventory_id_format_part')]
#[ORM\UniqueConstraint(name: 'uniq_inventory_format_position', columns: ['inventory_id', 'position'])]
class InventoryIdFormatPart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Инвентарь, к которому относится эта часть формата.
     */
    #[ORM\ManyToOne(targetEntity: Inventory::class, inversedBy: 'idFormatParts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    /**
     * Порядковый номер части в формате.
     */
    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    /**
     * Тип части идентификатора (статический текст, дата, случайное значение и т.д.).
     * enumType хранит значение enum в БД.
     */
    #[ORM\Column(enumType: InventoryIdPartType::class)]
    private InventoryIdPartType $type;

    /**
     * Дополнительный параметр 1 для генерации части (например, формат даты).
     */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $param1 = null;

    /**
     * Дополнительный параметр 2 для генерации части.
     */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $param2 = null;

    // ---------------- getters / setters ----------------

    /**
     * Идентификатор части формата.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Возвращает инвентарь.
     */
    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    /**
     * Устанавливает инвентарь.
     */
    public function setInventory(Inventory $inventory): self
    {
        $this->inventory = $inventory;
        return $this;
    }

    /**
     * Возвращает позицию.
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Устанавливает позицию.
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Возвращает тип части.
     */
    public function getType(): InventoryIdPartType
    {
        return $this->type;
    }

    /**
     * Устанавливает тип части.
     */
    public function setType(InventoryIdPartType $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Возвращает параметр 1.
     */
    public function getParam1(): ?string
    {
        return $this->param1;
    }

    /**
     * Устанавливает параметр 1.
     */
    public function setParam1(?string $param1): self
    {
        $this->param1 = $param1 !== null ? trim($param1) : null;
        if ($this->param1 === '') {
            $this->param1 = null;
        }
        return $this;
    }

    /**
     * Возвращает параметр 2.
     */
    public function getParam2(): ?string
    {
        return $this->param2;
    }

    /**
     * Устанавливает параметр 2.
     */
    public function setParam2(?string $param2): self
    {
        $this->param2 = $param2 !== null ? trim($param2) : null;
        if ($this->param2 === '') {
            $this->param2 = null;
        }
        return $this;
    }
}
