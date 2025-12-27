<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
#[ORM\Table(name: 'inventories')]
#[ORM\Index(name: 'idx_inventory_is_public', columns: ['is_public'])]
class Inventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Владелец инвентаря
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $owner;

    /**
     * Название инвентаря
     */
    #[ORM\Column(length: 255)]
    private string $name;

    /**
     * Публичный ли инвентарь
     */
    #[ORM\Column(name: 'is_public', type: 'boolean')]
    private bool $isPublic = false;

    /**
     * Optimistic locking
     */
    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version = 1;

    /**
     * Формат кастомного ID (упорядоченные части)
     */
    #[ORM\OneToMany(
        mappedBy: 'inventory',
        targetEntity: InventoryIdFormatPart::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $idFormatParts;

    public function __construct()
    {
        $this->idFormatParts = new ArrayCollection();
    }

    // ---------------- getters / setters ----------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return Collection<int, InventoryIdFormatPart>
     */
    public function getIdFormatParts(): Collection
    {
        return $this->idFormatParts;
    }
}
