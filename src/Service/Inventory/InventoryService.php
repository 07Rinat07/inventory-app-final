<?php

declare(strict_types=1);

namespace App\Service\Inventory;

use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\InventoryRepository;

final class InventoryService
{
    public function __construct(
        private InventoryRepository $repository,
    ) {}

    /**
     * @return Inventory[]
     */
    public function getInventoriesForUser(User $user): array
    {
        return $this->repository->findAvailableForUser($user);
    }

    public function create(User $user, string $name, bool $isPublic): Inventory
    {
        $inventory = new Inventory();
        $inventory->setOwner($user);
        $inventory->setName($name);
        $inventory->setIsPublic($isPublic);

        $this->repository->save($inventory);

        return $inventory;
    }
}
