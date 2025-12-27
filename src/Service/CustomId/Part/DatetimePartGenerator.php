<?php

declare(strict_types=1);

namespace App\Service\CustomId\Part;

use App\Domain\Policy\InventoryIdPartGeneratorInterface;
use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\InventoryIdFormatPart;

final class DatetimePartGenerator implements InventoryIdPartGeneratorInterface
{
    public function supports(InventoryIdFormatPart $part): bool
    {
        return $part->getType() === InventoryIdPartType::DATETIME;
    }

    public function generate(InventoryIdFormatPart $part, ?int $sequenceValue): string
    {
        // param1 — формат (например: Y, Ym, Ymd, YmdHi)
        $format = trim((string) ($part->getParam1() ?? 'Ymd'));

        // Проверка «на дурака»: пустой формат запрещён
        if ($format === '') {
            throw new \LogicException('DATETIME part requires param1 (date format).');
        }

        // Для курса достаточно системного времени; позже можно заменить на ClockInterface
        return (new \DateTimeImmutable())->format($format);
    }
}
