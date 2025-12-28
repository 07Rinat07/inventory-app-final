<?php

declare(strict_types=1);

namespace App\Dto\InventoryIdFormat;

final class InventoryIdFormatEditDto
{
    /**
     * @param InventoryIdFormatPartDto[] $parts
     */
    public function __construct(
        public int $inventoryId,
        public string $inventoryName,
        public array $parts = [],
    ) {}
}
