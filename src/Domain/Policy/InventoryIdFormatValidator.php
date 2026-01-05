<?php

declare(strict_types=1);

namespace App\Domain\Policy;

use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\Inventory;
use App\Entity\InventoryIdFormatPart;

/**
 * Валидатор формата кастомного идентификатора инвентаря.
 * Проверяет бизнес-правила формирования ID.
 */
final class InventoryIdFormatValidator
{
    /**
     * Валидирует формат идентификатора для инвентаря.
     *
     * @param Inventory $inventory Инвентарь.
     * @throws \DomainException Если формат некорректен.
     */
    public function validate(Inventory $inventory): void
    {
        $parts = $inventory->getIdFormatParts();

        if ($parts->isEmpty()) {
            throw new \DomainException('Inventory ID format is empty.');
        }

        $seqCount = 0;
        $positions = [];

        /** @var InventoryIdFormatPart $part */
        foreach ($parts as $part) {
            $positions[] = $part->getPosition();

            if ($part->getType() === InventoryIdPartType::SEQ) {
                $seqCount++;
            }
        }

        if ($seqCount !== 1) {
            throw new \DomainException('Inventory ID format must contain exactly one SEQ part.');
        }

        if (count($positions) !== count(array_unique($positions))) {
            throw new \DomainException('Inventory ID format positions must be unique.');
        }
    }
}
