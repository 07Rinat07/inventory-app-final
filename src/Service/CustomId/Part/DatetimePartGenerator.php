<?php

declare(strict_types=1);

namespace App\Service\CustomId\Part;

use App\Domain\Policy\InventoryIdPartGeneratorInterface;
use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\InventoryIdFormatPart;

/**
 * Генератор части идентификатора на основе текущей даты и времени.
 */
final class DatetimePartGenerator implements InventoryIdPartGeneratorInterface
{
    /**
     * Проверяет, поддерживает ли данный генератор указанную часть формата.
     */
    public function supports(InventoryIdFormatPart $part): bool
    {
        return $part->getType() === InventoryIdPartType::DATETIME;
    }

    /**
     * Генерирует строковое представление даты по заданному формату.
     *
     * @param InventoryIdFormatPart $part Часть формата.
     * @param int|null $sequenceValue Значение последовательности (не используется в этом генераторе).
     * @return string Сгенерированная дата.
     * @throws \LogicException Если формат даты не указан.
     */
    public function generate(InventoryIdFormatPart $part, ?int $sequenceValue): string
    {
        // param1 — формат (например: Y, Ym, Ymd, YmdHi)
        $format = trim((string) ($part->getParam1() ?? 'Ymd'));

        if ($format === '') {
            throw new \LogicException('DATETIME part requires param1 (date format).');
        }

        return (new \DateTimeImmutable())->format($format);
    }
}
