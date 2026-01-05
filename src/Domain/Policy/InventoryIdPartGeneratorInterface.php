<?php

declare(strict_types=1);

namespace App\Domain\Policy;

use App\Entity\InventoryIdFormatPart;

/**
 * Интерфейс для генераторов частей кастомного идентификатора инвентаря.
 */
interface InventoryIdPartGeneratorInterface
{
    /**
     * Проверяет, поддерживает ли данный генератор указанную часть формата.
     *
     * @param InventoryIdFormatPart $part Часть формата.
     * @return bool True, если поддерживает.
     */
    public function supports(InventoryIdFormatPart $part): bool;

    /**
     * Генерирует строковое значение для части идентификатора.
     *
     * @param InventoryIdFormatPart $part Часть формата.
     * @param int|null $sequenceValue Текущее значение последовательности (SEQ) или null.
     * @return string Сгенерированная строка.
     */
    public function generate(InventoryIdFormatPart $part, ?int $sequenceValue): string;
}
