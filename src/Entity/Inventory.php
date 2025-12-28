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
     * Владелец инвентаря.
     * ManyToOne: много инвентарей принадлежат одному пользователю.
     * inversedBy полезен, если в User есть коллекция inventories.
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'inventories')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $owner;

    /**
     * Название инвентаря.
     */
    #[ORM\Column(length: 255)]
    private string $name = '';

    /**
     * Публичный ли инвентарь.
     */
    #[ORM\Column(name: 'is_public', type: 'boolean')]
    private bool $isPublic = false;

    /**
     * Optimistic locking.
     */
    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version = 1;

    /**
     * Формат кастомного ID (упорядоченные части).
     *
     * orphanRemoval=true:
     * - если Part убрать из коллекции и сделать flush — строка удалится из БД.
     */
    #[ORM\OneToMany(
        mappedBy: 'inventory',
        targetEntity: InventoryIdFormatPart::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    /** @var Collection<int, InventoryIdFormatPart> */
    private Collection $idFormatParts;

    /**
     * Кастомные поля инвентаря.
     *
     * orphanRemoval=true:
     * - если CustomField убрать из коллекции и сделать flush — строка удалится из БД.
     */
    #[ORM\OneToMany(
        mappedBy: 'inventory',
        targetEntity: CustomField::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    /** @var Collection<int, CustomField> */
    private Collection $customFields;

    public function __construct()
    {
        $this->idFormatParts = new ArrayCollection();
        $this->customFields = new ArrayCollection();
    }

    // ---------------- base getters / setters ----------------

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
        $this->name = trim($name);
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

    // ---------------- ID format parts ----------------

    /**
     * @return Collection<int, InventoryIdFormatPart>
     */
    public function getIdFormatParts(): Collection
    {
        return $this->idFormatParts;
    }

    public function addIdFormatPart(InventoryIdFormatPart $part): self
    {
        if (!$this->idFormatParts->contains($part)) {
            $this->idFormatParts->add($part);
            // owning-side (в InventoryIdFormatPart) должен ссылаться на Inventory
            $part->setInventory($this);
        }

        return $this;
    }

    public function removeIdFormatPart(InventoryIdFormatPart $part): self
    {
        // НЕ делаем $part->setInventory(null) — joinColumn nullable=false.
        // orphanRemoval=true удалит строку из БД при flush.
        $this->idFormatParts->removeElement($part);

        return $this;
    }

    // ---------------- Custom fields ----------------

    /**
     * @return Collection<int, CustomField>
     */
    public function getCustomFields(): Collection
    {
        return $this->customFields;
    }

    public function addCustomField(CustomField $customField): self
    {
        if (!$this->customFields->contains($customField)) {
            $this->customFields->add($customField);
            // owning-side (в CustomField) должен ссылаться на Inventory
            $customField->setInventory($this);
        }

        return $this;
    }

    public function removeCustomField(CustomField $customField): self
    {
        // НЕ делаем $customField->setInventory(null) — joinColumn nullable=false.
        // orphanRemoval=true удалит строку из БД при flush.
        $this->customFields->removeElement($customField);

        return $this;
    }
}
