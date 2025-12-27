<?php

declare(strict_types=1);

namespace App\Dto\Inventory;

/**
 * DTO для редактирования формата Custom ID инвентаря.
 *
 * Используется:
 * - в Twig UI (FORMAT_UI)
 * - в будущем в API Platform (read/write)
 *
 * НЕ содержит бизнес-логики.
 */
final class InventoryIdFormatDto
{
    /**
     * @var InventoryIdFormatPartDto[]
     */
    private array $parts = [];

    /**
     * @return InventoryIdFormatPartDto[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    public function addPart(InventoryIdFormatPartDto $part): void
    {
        $this->parts[] = $part;
    }

    /**
     * Полная замена частей (используется при reorder/save)
     *
     * @param InventoryIdFormatPartDto[] $parts
     */
    public function replaceParts(array $parts): void
    {
        $this->parts = $parts;
    }
}
