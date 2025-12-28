<?php

declare(strict_types=1);

namespace App\Service\CustomId;

use App\Entity\Inventory;
use App\Entity\InventoryIdFormatPart;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Генератор custom_id для InventoryItem.
 *
 * Почему так:
 * - InventoryItemController ожидает сервис InventoryItemIdGenerator::generate($inventory)
 * - Формат хранится в inventory_id_format_part и доступен через $inventory->getIdFormatParts()
 *
 * Алгоритм:
 * - если формат пустой -> fallback "INV{inventoryId}-{seq}"
 * - если формат задан -> собираем строку по частям
 */
final class InventoryItemIdGenerator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function generate(Inventory $inventory): string
    {
        /** @var InventoryIdFormatPart[] $parts */
        $parts = $inventory->getIdFormatParts()->toArray();

        // Если формат ещё не настроен — делаем “безопасный” fallback
        if ($parts === []) {
            $seq = $this->nextSequence($inventory);
            return sprintf('INV%d-%d', (int) $inventory->getId(), $seq);
        }

        $chunks = [];

        foreach ($parts as $part) {
            // Enum InventoryIdPartType (backed string) -> берём value строкой
            $typeValue = method_exists($part->getType(), 'value')
                ? $part->getType()->value
                : (string) $part->getType();

            $typeValue = trim((string) $typeValue);

            if ($typeValue === '') {
                continue;
            }

            // param1/param2 — зависят от типа (в UI ты их редактируешь)
            $p1 = $part->getParam1();
            $p2 = $part->getParam2();

            // Собираем кусок по типу
            $chunk = match ($typeValue) {
                // Примеры поддерживаемых строк (типичные):
                'inventory' => (string) $inventory->getId(),

                // Статический текст (param1 = текст)
                'text', 'static' => (string) ($p1 ?? ''),

                // Дата (param1 = формат даты, по умолчанию Ymd)
                'date' => (new \DateTimeImmutable())->format($p1 ?: 'Ymd'),

                // Последовательность (param1 = pad length, param2 = prefix optional)
                'sequence', 'seq' => $this->formatSequence(
                    inventory: $inventory,
                    pad: $p1,
                    prefix: $p2
                ),

                // Если тип неизвестен — ничего не добавляем (без падения)
                default => '',
            };

            if ($chunk !== '') {
                $chunks[] = $chunk;
            }
        }

        // Если по какой-то причине всё пропустили — fallback
        if ($chunks === []) {
            $seq = $this->nextSequence($inventory);
            return sprintf('INV%d-%d', (int) $inventory->getId(), $seq);
        }

        // Разделитель можно “зашить” в формате через static/text части (например "-")
        return implode('', $chunks);
    }

    private function formatSequence(Inventory $inventory, ?string $pad, ?string $prefix): string
    {
        $seq = $this->nextSequence($inventory);

        // pad — сколько символов (например "5" -> 00001)
        $padLen = (int) ($pad ?: 0);
        $seqStr = ($padLen > 0)
            ? str_pad((string) $seq, $padLen, '0', STR_PAD_LEFT)
            : (string) $seq;

        $pref = (string) ($prefix ?? '');

        return $pref . $seqStr;
    }

    /**
     * Получаем следующее значение sequence для inventory.
     *
     * ВАЖНО:
     * - у тебя в миграциях видно таблицу/sequence под inventory_sequence,
     *   и UNIQUE по inventory_id (в миграциях было "uniq_inventory_sequence").
     * - поэтому делаем UPSERT (PostgreSQL) и возвращаем current_value.
     *
     * Если таблица вдруг называется иначе — скажешь, подправим SQL одной строкой.
     */
    private function nextSequence(Inventory $inventory): int
    {
        $inventoryId = (int) $inventory->getId();

        if ($inventoryId <= 0) {
            // На всякий: inventory ещё не сохранён
            return random_int(1000, 9999);
        }

        $conn = $this->em->getConnection();

        // PostgreSQL upsert + returning
        $sql = <<<SQL
        INSERT INTO inventory_sequence (inventory_id, current_value)
        VALUES (:inv, 1)
        ON CONFLICT (inventory_id)
        DO UPDATE SET current_value = inventory_sequence.current_value + 1
        RETURNING current_value
        SQL;

        $value = $conn->fetchOne($sql, ['inv' => $inventoryId]);

        return (int) $value;
    }
}
