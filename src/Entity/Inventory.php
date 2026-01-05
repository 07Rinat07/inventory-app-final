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

    /**
     * Создает новый объект инвентаря.
     */
    public function __construct()
    {
        $this->idFormatParts = new ArrayCollection();
        $this->customFields = new ArrayCollection();
    }

    // ---------------- base getters / setters ----------------

    /**
     * Идентификатор инвентаря.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Возвращает владельца инвентаря.
     */
    public function getOwner(): User
    {
        return $this->owner;
    }

    /**
     * Ставим владельца инвентаря.
     */
    public function setOwner(User $owner): self
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * Название инвентаря.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Меняем название, убирая лишние пробелы.
     */
    public function setName(string $name): self
    {
        $this->name = trim($name);
        return $this;
    }

    /**
     * Открытый (публичный) инвентарь или нет.
     */
    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    /**
     * Меняем статус публичности.
     */
    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    /**
     * Возвращает версию записи.
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    // ---------------- ID format parts ----------------

    /**
     * Возвращает коллекцию частей формата идентификатора.
     * @return Collection<int, InventoryIdFormatPart>
     */
    public function getIdFormatParts(): Collection
    {
        return $this->idFormatParts;
    }

    /**
     * Добавляет часть формата идентификатора.
     *
     * @param InventoryIdFormatPart $part Часть формата.
     * @return $this
     */
    public function addIdFormatPart(InventoryIdFormatPart $part): self
    {
        if (!$this->idFormatParts->contains($part)) {
            $this->idFormatParts->add($part);
            // owning-side (в InventoryIdFormatPart) должен ссылаться на Inventory
            $part->setInventory($this);
        }

        return $this;
    }

    /**
     * Удаляет часть формата идентификатора.
     *
     * @param InventoryIdFormatPart $part Часть формата.
     * @return $this
     */
    public function removeIdFormatPart(InventoryIdFormatPart $part): self
    {
        // НЕ делаем $part->setInventory(null) — joinColumn nullable=false.
        // orphanRemoval=true удалит строку из БД при flush.
        $this->idFormatParts->removeElement($part);

        return $this;
    }

    // ---------------- Custom fields ----------------

    /**
     * Возвращает коллекцию кастомных полей.
     * @return Collection<int, CustomField>
     */
    public function getCustomFields(): Collection
    {
        return $this->customFields;
    }

    /**
     * Добавляем кастомное поле к инвентарю.
     * Не забываем про owning-side в CustomField.
     */
    public function addCustomField(CustomField $customField): self
    {
        if (!$this->customFields->contains($customField)) {
            $this->customFields->add($customField);
            $customField->setInventory($this);
        }

        return $this;
    }

    /**
     * Удаляем кастомное поле.
     * Саму строку в БД удалит Doctrine через orphanRemoval.
     */
    public function removeCustomField(CustomField $customField): self
    {
        $this->customFields->removeElement($customField);

        return $this;
    }
}
