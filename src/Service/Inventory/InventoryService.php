<?php

declare(strict_types=1);

namespace App\Service\Inventory;

use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\InventoryRepository;

/**
 * Сервис для управления инвентарями.
 */
final class InventoryService
{
    /**
     * Создает новый экземпляр сервиса.
     */
    public function __construct(
        private InventoryRepository $repository,
    ) {}

    /**
     * Возвращает список инвентарей, доступных пользователю.
     *
     * @param User $user Пользователь.
     * @return Inventory[] Список инвентарей.
     */
    public function getInventoriesForUser(User $user): array
    {
        return $this->repository->findAvailableForUser($user);
    }

    /**
     * Создает новый инвентарь.
     *
     * @param User $owner Владелец инвентаря.
     * @param string $name Название инвентаря.
     * @param bool $isPublic Является ли инвентарь публичным.
     * @return Inventory Созданный объект инвентаря.
     */
    public function create(User $owner, string $name, bool $isPublic): Inventory
    {
        $inventory = new Inventory();
        $inventory->setOwner($owner);
        $inventory->setName($name);
        $inventory->setIsPublic($isPublic);

        // Сохраняем и сразу применяем изменения (flush: true)
        $this->repository->save($inventory, flush: true);

        return $inventory;
    }

    /**
     * Обновляет данные существующего инвентаря.
     *
     * @param Inventory $inventory Объект инвентаря.
     * @param string $name Новое название.
     * @param bool $isPublic Новый статус публичности.
     */
    public function update(Inventory $inventory, string $name, bool $isPublic): void
    {
        $inventory->setName($name);
        $inventory->setIsPublic($isPublic);

        $this->repository->save($inventory, flush: true);
    }

    /**
     * Удаляет инвентарь.
     *
     * @param Inventory $inventory Объект инвентаря для удаления.
     */
    public function delete(Inventory $inventory): void
    {
        $this->repository->remove($inventory, flush: true);
    }
}
