<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventorySequenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventorySequenceRepository::class)]
#[ORM\Table(name: 'inventory_sequence')]
#[ORM\UniqueConstraint(
    name: 'uniq_inventory_sequence_inventory',
    columns: ['inventory_id']
)]
class InventorySequence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    #[ORM\Column(name: 'next_value')]
    private int $nextValue = 1;

    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    /**
     * Возвращает следующий номер и увеличивает счётчик.
     *
     * ВАЖНО:
     * - вызывать ТОЛЬКО внутри транзакции
     * - безопасно при SELECT FOR UPDATE
     */
    public function next(): int
    {
        $current = $this->nextValue;
        $this->nextValue++;

        return $current;
    }
}
