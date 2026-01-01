<?php

declare(strict_types=1);

namespace App\Service\CustomId;

use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\Inventory;
use App\Entity\InventoryIdFormatPart;
use App\Repository\InventoryIdFormatPartRepository;
use Doctrine\ORM\EntityManagerInterface;

final class InventoryIdFormatService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InventoryIdFormatPartRepository $partsRepository,
    ) {}

    /**
     * Для формы редактирования.
     * Если пока нет частей — возвращаем одну "пустую" строку, чтобы UI не был пустым.
     *
     * ВАЖНО:
     * - dummy НЕ сохраняется в БД
     * - inventory НЕ задаём специально, потому что это “строка формы”, а не сущность домена
     *
     * @return InventoryIdFormatPart[]
     */
    public function getPartsForEdit(Inventory $inventory): array
    {
        // Желательно получать отсортированно (на случай, если коллекция не гарантирует order)
        $parts = $this->partsRepository->findOrderedByInventory($inventory);

        if ($parts === []) {
            $dummy = new InventoryIdFormatPart();
            $dummy->setPosition(0);

            // безопасный дефолт: первый enum-case
            $dummy->setType(InventoryIdPartType::cases()[0]);

            $dummy->setParam1('');
            $dummy->setParam2('');

            return [$dummy];
        }

        return $parts;
    }

    /**
     * Полностью заменяем формат тем, что пришло из формы.
     *
     * Почему так:
     * - есть уникальный индекс (inventory_id, position)
     * - при “replace-all” проще и надёжнее удалить всё и вставить заново в транзакции
     * - исключаем конфликт позиций и “полуобновление”
     *
     * @param array<int, mixed> $types
     * @param array<int, mixed> $param1
     * @param array<int, mixed> $param2
     */
    public function replaceFromForm(Inventory $inventory, array $types, array $param1, array $param2): void
    {
        $this->em->wrapInTransaction(function () use ($inventory, $types, $param1, $param2): void {
            /**
             * 1) Удаляем старые части формата одним SQL DELETE.
             *
             * Критично:
             * - это удаление выполняется сразу в БД (не откладывается до flush),
             *   поэтому при вставке новых частей не будет конфликта UNIQUE (inventory_id, position).
             */
            $this->partsRepository->deleteByInventory($inventory);

            /**
             * 2) Синхронизируем объектную сторону (коллекцию внутри Inventory),
             * чтобы в памяти не оставались старые объекты (которые уже удалены SQL-ом).
             *
             * Если в Inventory реализованы add/remove — используем их.
             */
            foreach ($inventory->getIdFormatParts() as $existing) {
                $inventory->removeIdFormatPart($existing);
            }

            /**
             * 3) Создаём новые части.
             * Позиции считаем только по реально добавленным строкам (без пустых).
             */
            $position = 0;

            foreach ($types as $i => $rawType) {
                $rawType = is_string($rawType) ? trim($rawType) : '';

                // Пустая строка = строка формы без выбора -> пропускаем
                if ($rawType === '') {
                    continue;
                }

                $type = InventoryIdPartType::tryFrom($rawType);
                if ($type === null) {
                    // неизвестное значение enum -> пропускаем, не падаем
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

                // держим обе стороны связи синхронно
                $inventory->addIdFormatPart($part);

                $this->em->persist($part);
                $position++;
            }

            /**
             * 4) Один flush в конце.
             * старые строки уже удалены в БД на шаге (1).
             */
            $this->em->flush();
        });
    }
}
