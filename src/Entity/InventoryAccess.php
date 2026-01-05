<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryAccessRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Представляет права доступа пользователя к инвентарю.
 */
#[ORM\Entity(repositoryClass: InventoryAccessRepository::class)]
#[ORM\Table(name: 'inventory_access')]
#[ORM\UniqueConstraint(name: 'uniq_inventory_user', columns: ['inventory_id', 'user_id'])]
class InventoryAccess
{
    /** Права на чтение */
    public const PERMISSION_READ  = 'READ';
    /** Права на запись/изменение */
    public const PERMISSION_WRITE = 'WRITE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Инвентарь, к которому предоставляется доступ.
     */
    #[ORM\ManyToOne(targetEntity: Inventory::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    /**
     * Пользователь, которому предоставляется доступ.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    /**
     * Уровень доступа (READ или WRITE).
     */
    #[ORM\Column(length: 10, nullable: false)]
    private string $permission;

    // -------- getters / setters --------

    /**
     * Идентификатор записи о доступе.
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
     * Возвращает пользователя.
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Устанавливает пользователя.
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Возвращает уровень доступа.
     */
    public function getPermission(): string
    {
        return $this->permission;
    }

    /**
     * Устанавливает уровень доступа.
     * @throws \InvalidArgumentException если передан некорректный уровень доступа.
     */
    public function setPermission(string $permission): self
    {
        if (!\in_array($permission, [self::PERMISSION_READ, self::PERMISSION_WRITE], true)) {
            throw new \InvalidArgumentException('Invalid inventory permission');
        }

        $this->permission = $permission;
        return $this;
    }
}
