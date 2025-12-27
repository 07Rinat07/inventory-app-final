<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

/**
 * Тип части кастомного Inventory ID
 *
 * Используется:
 * - в Entity InventoryIdFormatPart
 * - в генераторах ID
 * - в валидаторе формата
 */
enum InventoryIdPartType: string
{
    /**
     * Фиксированная строка
     * Пример: "INV", "ITEM", "SKU"
     * param1 = текст
     */
    case FIXED = 'FIXED';

    /**
     * Последовательный номер (из InventorySequence)
     * param1 = длина (padding), например 5 → 00001
     */
    case SEQ = 'SEQ';

    /**
     * Дата / время
     * param1 = формат (Y, Ym, Ymd, YmdHi и т.п.)
     */
    case DATETIME = 'DATETIME';

    /**
     * Случайная строка
     * param1 = длина
     * param2 = алфавит (optional)
     */
    case RANDOM = 'RANDOM';
}
