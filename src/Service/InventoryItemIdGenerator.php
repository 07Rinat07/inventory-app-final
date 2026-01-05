<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Inventory;
use App\Entity\InventorySequence;
use App\Repository\InventorySequenceRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Генератор простых числовых ID для предметов (просто по порядку).
 */
final class InventoryItemIdGenerator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InventorySequenceRepository $sequenceRepository,
    ) {}

    /**
     * Выдает следующий номер для предмета в конкретном инвентаре.
     * Тут используется блокировка FOR UPDATE, чтобы при большой нагрузке номера не двоились.
     */
    public function generate(Inventory $inventory): int
    {
        return $this->em->wrapInTransaction(function () use ($inventory): int {
            // Ищем текущий счетчик с блокировкой
            $sequence = $this->sequenceRepository->findForUpdate($inventory);

            // Если его еще нет — создаем новый
            if ($sequence === null) {
                $sequence = new InventorySequence();
                $sequence->setInventory($inventory);
                $this->em->persist($sequence);
            }

            // Берем номер и сразу плюсуем на будущее
            $value = $sequence->getNextValue();
            $sequence->increment();

            return $value;
        });
    }
}
