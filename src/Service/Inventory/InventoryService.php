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

    public function create(User $owner, string $name, bool $isPublic): Inventory
    {
        $inventory = new Inventory();
        $inventory->setOwner($owner);
        $inventory->setName($name);
        $inventory->setIsPublic($isPublic);

        // Важно: flush здесь, иначе в БД не появится запись.
        $this->repository->save($inventory, flush: true);

        return $inventory;
    }

    public function update(Inventory $inventory, string $name, bool $isPublic): void
    {
        $inventory->setName($name);
        $inventory->setIsPublic($isPublic);

        $this->repository->save($inventory, flush: true);
    }

    public function delete(Inventory $inventory): void
    {
        $this->repository->remove($inventory, flush: true);
    }
}
