<?php

declare(strict_types=1);

namespace App\Service\CustomId\Part;

use App\Domain\Policy\InventoryIdPartGeneratorInterface;
use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\InventoryIdFormatPart;

final class FixedPartGenerator implements InventoryIdPartGeneratorInterface
{
    public function supports(InventoryIdFormatPart $part): bool
    {
        return $part->getType() === InventoryIdPartType::FIXED;
    }

    public function generate(InventoryIdFormatPart $part, ?int $sequenceValue): string
    {
        $value = trim((string) ($part->getParam1() ?? ''));

        if ($value === '') {
            throw new \LogicException('FIXED part requires param1 (non-empty text).');
        }

        return $value;
    }
}
