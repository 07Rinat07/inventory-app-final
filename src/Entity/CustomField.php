<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\CustomField\CustomFieldType;
use App\Repository\CustomFieldRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Представляет кастомное поле инвентаря.
 */
#[ORM\Entity(repositoryClass: CustomFieldRepository::class)]
#[ORM\Table(name: 'custom_fields')]
#[ORM\UniqueConstraint(name: 'uniq_custom_fields_position', columns: ['inventory_id', 'position'])]
#[ORM\Index(name: 'idx_custom_fields_inventory', columns: ['inventory_id'])]
class CustomField
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')] // PostgreSQL выдаёт id через IDENTITY
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Инвентарь, которому принадлежит поле.
     * Owning side.
     * nullable=false + onDelete=CASCADE => поле удаляется вместе с инвентарём.
     */
    #[ORM\ManyToOne(targetEntity: Inventory::class, inversedBy: 'customFields')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    /**
     * Тип поля (текст, число и т.д.).
     * Храним строку в БД, наружу отдаём enum.
     */
    #[ORM\Column(type: 'string', length: 20)]
    private string $type;

    /**
     * Позиция поля при отображении.
     */
    #[ORM\Column(type: 'integer')]
    private int $position;

    /**
     * Является ли поле обязательным для заполнения.
     */
    #[ORM\Column(name: 'is_required', type: 'boolean', options: ['default' => false])]
    private bool $isRequired = false;

    /**
     * Создает новое кастомное поле.
     * Обязательные поля задаём через constructor,
     * чтобы сущность не могла существовать в “полусобранном” состоянии.
     */
    public function __construct(Inventory $inventory, CustomFieldType $type, int $position)
    {
        $this->inventory = $inventory;
        $this->type = $type->value;
        $this->position = $position;
    }

    // ---------------- getters / setters ----------------

    /**
     * Идентификатор поля.
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
     * Возвращает тип поля.
     */
    public function getType(): CustomFieldType
    {
        return CustomFieldType::from($this->type);
    }

    /**
     * Устанавливает тип поля.
     */
    public function setType(CustomFieldType $type): self
    {
        $this->type = $type->value;
        return $this;
    }

    /**
     * Возвращает позицию поля.
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Устанавливает позицию поля.
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Проверяет, является ли поле обязательным.
     * Twig:
     * - f.isRequired  -> вызовет isRequired()
     * - f.required    -> вызовет isRequired() (из-за правила get/is/has)
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * Устанавливает обязательность поля.
     */
    public function setIsRequired(bool $required): self
    {
        $this->isRequired = $required;
        return $this;
    }
}
