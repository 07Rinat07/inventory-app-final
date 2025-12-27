<?php

declare(strict_types=1);

namespace App\Service\CustomId\Part;

use App\Domain\Policy\InventoryIdPartGeneratorInterface;
use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\InventoryIdFormatPart;

final class SeqPartGenerator implements InventoryIdPartGeneratorInterface
{
    public function supports(InventoryIdFormatPart $part): bool
    {
        return $part->getType() === InventoryIdPartType::SEQ;
    }

    public function generate(InventoryIdFormatPart $part, ?int $sequenceValue): string
    {
        if ($sequenceValue === null) {
            throw new \LogicException('SEQ part requires sequenceValue (internal generator bug).');
        }

        // param1 — длина паддинга (например 5 → 00001)
        $pad = (int) ($part->getParam1() ?? 0);

        return $pad > 0
            ? str_pad((string) $sequenceValue, $pad, '0', STR_PAD_LEFT)
            : (string) $sequenceValue;
    }
}
