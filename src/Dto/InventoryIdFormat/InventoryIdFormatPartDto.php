<?php

declare(strict_types=1);

namespace App\Dto\InventoryIdFormat;

use App\Domain\ValueObject\InventoryIdPartType;

final class InventoryIdFormatPartDto
{
    public function __construct(
        public ?int $id,
        public int $position,
        public InventoryIdPartType $type,
        public ?string $param1,
        public ?string $param2,
    ) {}
}
