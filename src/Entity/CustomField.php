<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\CustomField\CustomFieldType;
use App\Repository\CustomFieldRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomFieldRepository::class)]
#[ORM\Table(
    name: 'custom_fields',
    uniqueConstraints: [
        /**
         * ВАЖНО:
         * Имя unique должно совпадать с тем, что реально есть/ожидается в БД.
         * Судя по твоему dump-sql, в БД индекс/constraint называется:
         *   uniq_custom_fields_position
         * поэтому фиксируем именно это имя, иначе doctrine:schema:validate будет ругаться
         * и migrations:diff будет пытаться "дропать/создавать" заново.
         */
        new ORM\UniqueConstraint(
            name: 'uniq_custom_fields_position',
            columns: ['inventory_id', 'position']
        ),
    ],
    indexes: [
        /**
         * Индекс на FK inventory_id почти всегда есть (и полезен по производительности).
         * Опять же: фиксируем имя, чтобы Doctrine не пытался переименовывать в IDX_********.
         */
        new ORM\Index(
            name: 'idx_custom_fields_inventory',
            columns: ['inventory_id']
        ),
    ]
)]
class CustomField
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')] // PostgreSQL выдаёт id через IDENTITY
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Owning side.
     * nullable=false + onDelete=CASCADE => поле удаляется вместе с инвентарём.
     */
    #[ORM\ManyToOne(targetEntity: Inventory::class, inversedBy: 'customFields')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    /**
     * Храним строку в БД, наружу отдаём enum.
     */
    #[ORM\Column(type: 'string', length: 20)]
    private string $type;

    #[ORM\Column(type: 'integer')]
    private int $position;

    #[ORM\Column(name: 'is_required', type: 'boolean', options: ['default' => false])]
    private bool $isRequired = false;

    /**
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    /**
     * Если тебе нужно менять inventory у поля (обычно не нужно),
     * то можно добавить setInventory(). Сейчас оставляю без него намеренно
     * (меньше риска “перетаскивать” поля между inventory).
     */

    public function getType(): CustomFieldType
    {
        return CustomFieldType::from($this->type);
    }

    public function setType(CustomFieldType $type): self
    {
        $this->type = $type->value;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Twig:
     * - f.isRequired  -> вызовет isRequired()
     * - f.required    -> вызовет isRequired() (из-за правила get/is/has)
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $required): self
    {
        $this->isRequired = $required;
        return $this;
    }
}
