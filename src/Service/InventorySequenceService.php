<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Inventory;
use App\Entity\InventorySequence;
use App\Repository\InventorySequenceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class InventorySequenceService
{
    public function __construct(
        private EntityManagerInterface $em,
        private InventorySequenceRepository $repository,
    ) {}

    /**
     * Возвращает следующий sequence value для Inventory.
     * Гарантирует уникальность (SELECT FOR UPDATE).
     */
    public function nextValue(Inventory $inventory): int
    {
        return $this->em->wrapInTransaction(function () use ($inventory): int {
            $sequence = $this->repository->findForUpdate($inventory);

            if ($sequence === null) {
                $sequence = new InventorySequence();
                $sequence->setInventory($inventory);
                $sequence->setNextValue(1);

                $this->em->persist($sequence);
            }

            $current = $sequence->getNextValue();
            $sequence->increment();

            return $current;
        });
    }
}
