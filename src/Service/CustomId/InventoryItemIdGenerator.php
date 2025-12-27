<?php

declare(strict_types=1);

namespace App\Service\CustomId;

use App\Domain\Policy\InventoryIdFormatValidator;
use App\Domain\Policy\InventoryIdPartGeneratorInterface;
use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\Inventory;
use App\Entity\InventoryIdFormatPart;
use App\Service\InventorySequenceService;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final class InventoryItemIdGenerator
{
    /**
     * @param iterable<InventoryIdPartGeneratorInterface> $partGenerators
     */
    public function __construct(
        private InventorySequenceService $sequenceService,
        private InventoryIdFormatValidator $validator,
        #[TaggedIterator('app.inventory_id_part_generator')]
        private iterable $partGenerators
    ) {}

    public function generate(Inventory $inventory): string
    {
        // 1. Проверяем формат
        $this->validator->validate($inventory);

        // 2. Получаем sequence (один раз!)
        $sequenceValue = $this->sequenceService->nextValue($inventory);

        // 3. Собираем ID
        $result = '';

        foreach ($inventory->getIdFormatParts() as $part) {
            $result .= $this->generatePart($part, $sequenceValue);
        }

        return $result;
    }

    private function generatePart(
        InventoryIdFormatPart $part,
        int $sequenceValue
    ): string {
        foreach ($this->partGenerators as $generator) {
            if ($generator->supports($part)) {
                return $generator->generate(
                    $part,
                    $part->getType() === InventoryIdPartType::SEQ
                        ? $sequenceValue
                        : null
                );
            }
        }

        throw new \LogicException(
            sprintf('No generator found for part type "%s".', $part->getType()->value)
        );
    }
}
