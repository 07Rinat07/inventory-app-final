<?php

declare(strict_types=1);

namespace App\Service\CustomId;

use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\Inventory;
use App\Entity\InventoryIdFormatPart;
use Doctrine\ORM\EntityManagerInterface;

final class InventoryIdFormatService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * Для формы редактирования.
     * Если пока нет частей — возвращаем одну "пустую" строку, чтобы UI не был пустым.
     *
     * @return InventoryIdFormatPart[]
     */
    public function getPartsForEdit(Inventory $inventory): array
    {
        $parts = $inventory->getIdFormatParts()->toArray();

        if ($parts === []) {
            // ВАЖНО: это не persist, просто “пустая строка” для UI
            $dummy = new InventoryIdFormatPart();
            $dummy->setPosition(0);
            $dummy->setType(InventoryIdPartType::tryFrom('text') ?? InventoryIdPartType::cases()[0]);
            $dummy->setParam1('');
            $dummy->setParam2('');
            return [$dummy];
        }

        return $parts;
    }

    /**
     * Полностью заменяем формат тем, что пришло из формы.
     *
     * @param array<int, mixed> $types
     * @param array<int, mixed> $param1
     * @param array<int, mixed> $param2
     */
    public function replaceFromForm(Inventory $inventory, array $types, array $param1, array $param2): void
    {
        // 1) Удаляем старые части
        foreach ($inventory->getIdFormatParts() as $existing) {
            $inventory->removeIdFormatPart($existing); // orphanRemoval=true
            $this->em->remove($existing);              // явно, чтобы без сюрпризов
        }

        // 2) Создаём новые
        $position = 0;

        foreach ($types as $i => $rawType) {
            $rawType = is_string($rawType) ? trim($rawType) : '';

            // Пустая строка = строка формы без выбора -> пропускаем
            if ($rawType === '') {
                continue;
            }

            $type = InventoryIdPartType::tryFrom($rawType);
            if ($type === null) {
                // неизвестный enum value -> пропускаем
                continue;
            }

            $part = new InventoryIdFormatPart();
            $part->setInventory($inventory);
            $part->setPosition($position);
            $part->setType($type);

            $p1 = $param1[$i] ?? null;
            $p2 = $param2[$i] ?? null;

            $part->setParam1(is_string($p1) ? trim($p1) : null);
            $part->setParam2(is_string($p2) ? trim($p2) : null);

            $inventory->addIdFormatPart($part);

            $this->em->persist($part);
            $position++;
        }

        // 3) Фиксируем изменения
        $this->em->flush();
    }
}
