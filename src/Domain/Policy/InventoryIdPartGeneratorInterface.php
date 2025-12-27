<?php

declare(strict_types=1);

namespace App\Domain\Policy;

use App\Entity\InventoryIdFormatPart;

interface InventoryIdPartGeneratorInterface
{
    public function supports(InventoryIdFormatPart $part): bool;

    /**
     * $sequenceValue — уже рассчитанное значение SEQ (или null, если формат не использует SEQ).
     */
    public function generate(InventoryIdFormatPart $part, ?int $sequenceValue): string;
}
