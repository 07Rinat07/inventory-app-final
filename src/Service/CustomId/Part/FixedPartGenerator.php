<?php

declare(strict_types=1);

namespace App\Service\CustomId\Part;

use App\Domain\Policy\InventoryIdPartGeneratorInterface;
use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\InventoryIdFormatPart;

/**
 * Генератор статической (фиксированной) части идентификатора.
 */
final class FixedPartGenerator implements InventoryIdPartGeneratorInterface
{
    /**
     * Проверяет, поддерживает ли данный генератор указанную часть формата.
     */
    public function supports(InventoryIdFormatPart $part): bool
    {
        return $part->getType() === InventoryIdPartType::FIXED;
    }

    /**
     * Возвращает фиксированный текст из параметров части формата.
     *
     * @param InventoryIdFormatPart $part Часть формата.
     * @param int|null $sequenceValue Значение последовательности (не используется).
     * @return string Фиксированное текстовое значение.
     * @throws \LogicException Если текстовое значение не указано.
     */
    public function generate(InventoryIdFormatPart $part, ?int $sequenceValue): string
    {
        $value = trim((string) ($part->getParam1() ?? ''));

        if ($value === '') {
            throw new \LogicException('FIXED part requires param1 (non-empty text).');
        }

        return $value;
    }
}
