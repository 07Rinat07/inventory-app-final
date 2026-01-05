<?php

declare(strict_types=1);

namespace App\Service\CustomId;

use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\Inventory;
use App\Entity\InventoryIdFormatPart;
use App\Repository\InventoryIdFormatPartRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Сервис для управления форматом кастомного идентификатора инвентаря.
 */
final class InventoryIdFormatService
{
    /**
     * Создает новый экземпляр сервиса.
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InventoryIdFormatPartRepository $partsRepository,
    ) {}

    /**
     * Возвращает части формата для редактирования.
     * Если части еще не настроены, возвращает пустую заготовку для интерфейса.
     *
     * @param Inventory $inventory Инвентарь.
     * @return InventoryIdFormatPart[] Список частей формата.
     */
    public function getPartsForEdit(Inventory $inventory): array
    {
        $parts = $this->partsRepository->findOrderedByInventory($inventory);

        if ($parts === []) {
            $dummy = new InventoryIdFormatPart();
            $dummy->setPosition(0);

            // Безопасный дефолт: первый доступный тип из Enum
            $dummy->setType(InventoryIdPartType::cases()[0]);

            $dummy->setParam1('');
            $dummy->setParam2('');

            return [$dummy];
        }

        return $parts;
    }

    /**
     * Полностью заменяет формат идентификатора данными из формы.
     *
     * Логика работы:
     * 1. Удаляет все существующие части формата в БД.
     * 2. Очищает коллекцию в объекте Inventory.
     * 3. Создает и сохраняет новые части формата.
     * Это гарантирует отсутствие конфликтов уникальных индексов (inventory_id, position).
     *
     * @param Inventory $inventory Инвентарь.
     * @param array<int, mixed> $types Типы частей.
     * @param array<int, mixed> $param1 Параметры 1.
     * @param array<int, mixed> $param2 Параметры 2.
     */
    public function replaceFromForm(Inventory $inventory, array $types, array $param1, array $param2): void
    {
        $this->em->wrapInTransaction(function () use ($inventory, $types, $param1, $param2): void {
            // 1) Удаляем старые части формата в БД
            $this->partsRepository->deleteByInventory($inventory);

            // 2) Синхронизируем коллекцию в памяти
            foreach ($inventory->getIdFormatParts() as $existing) {
                $inventory->removeIdFormatPart($existing);
            }

            // 3) Создаем новые части
            $position = 0;

            foreach ($types as $i => $rawType) {
                $rawType = is_string($rawType) ? trim($rawType) : '';

                if ($rawType === '') {
                    continue;
                }

                $type = InventoryIdPartType::tryFrom($rawType);
                if ($type === null) {
                    continue;
                }

                $part = new InventoryIdFormatPart();
                $part->setInventory($inventory);
                $part->setPosition($position);
                $part->setType($type);

                $p1 = $param1[$i] ?? null;
                $p2 = $param2[$i] ?? null;

                $part->setParam1(is_string($p1) ? $p1 : null);
                $part->setParam2(is_string($p2) ? $p2 : null);

                $inventory->addIdFormatPart($part);

                $this->em->persist($part);
                $position++;
            }

            // 4) Сохраняем все изменения
            $this->em->flush();
        });
    }
}
