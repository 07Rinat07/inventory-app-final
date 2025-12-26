<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryAccessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryAccessRepository::class)]
#[ORM\Table(name: 'inventory_access')]
#[ORM\UniqueConstraint(name: 'uniq_inventory_user', columns: ['inventory_id', 'user_id'])]
class InventoryAccess
{
    public const PERMISSION_READ  = 'READ';
    public const PERMISSION_WRITE = 'WRITE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Inventory::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 10, nullable: false)]
    private string $permission;

    // -------- getters / setters --------

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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }

    public function setPermission(string $permission): self
    {
        if (!\in_array($permission, [self::PERMISSION_READ, self::PERMISSION_WRITE], true)) {
            throw new \InvalidArgumentException('Invalid inventory permission');
        }

        $this->permission = $permission;
        return $this;
    }
}
