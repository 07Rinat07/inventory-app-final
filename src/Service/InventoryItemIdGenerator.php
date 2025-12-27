<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Inventory;
use App\Entity\InventorySequence;
use App\Repository\InventorySequenceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class InventoryItemIdGenerator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InventorySequenceRepository $sequenceRepository,
    ) {}

    /**
     * Генерирует следующий ID для элемента инвентаря.
     *
     * ВАЖНО:
     * - метод атомарный
     * - безопасен при конкурентном доступе
     * - единственная точка генерации ID
     */
    public function generate(Inventory $inventory): int
    {
        return $this->em->wrapInTransaction(function () use ($inventory): int {
            // 1. Берём sequence с блокировкой строки
            $sequence = $this->sequenceRepository
                ->findOneByInventoryForUpdate($inventory);

            // 2. Если sequence ещё не существует — создаём
            if ($sequence === null) {
                $sequence = new InventorySequence($inventory);
                $this->em->persist($sequence);
                $this->em->flush();
            }

            // 3. Получаем следующий номер
            $value = $sequence->next();

            // 4. Сохраняем новое состояние счётчика
            $this->em->flush();

            return $value;
        });
    }
}
