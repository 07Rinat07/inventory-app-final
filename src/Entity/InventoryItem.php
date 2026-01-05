<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/**
 * Представляет единицу (предмет) инвентаря.
 */
#[ORM\Entity(repositoryClass: InventoryItemRepository::class)]
#[ORM\Table(name: 'inventory_items')]
#[ORM\UniqueConstraint(
    name: 'uniq_inventory_custom_id',
    columns: ['inventory_id', 'custom_id']
)]
class InventoryItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Инвентарь, к которому относится данный предмет.
     */
    #[ORM\ManyToOne(targetEntity: Inventory::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    /**
     * Кастомный (сгенерированный) идентификатор предмета.
     */
    #[ORM\Column(name: 'custom_id', length: 255, nullable: false)]
    private string $customId;

    /**
     * Дата и время создания записи.
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    /**
     * Версия записи для оптимистической блокировки.
     */
    #[ORM\Version]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $version = 1;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    // -------- getters / setters --------

    /**
     * Идентификатор предмета.
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
     * Возвращает кастомный ID.
     */
    public function getCustomId(): string
    {
        return $this->customId;
    }

    /**
     * Устанавливает кастомный ID.
     */
    public function setCustomId(string $customId): self
    {
        $this->customId = $customId;
        return $this;
    }

    /**
     * Возвращает дату создания.
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Возвращает версию записи.
     */
    public function getVersion(): int
    {
        return $this->version;
    }
}
