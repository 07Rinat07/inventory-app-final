<?php

namespace App\Tests\Unit\Service;

use App\Entity\Inventory;
use App\Entity\InventorySequence;
use App\Repository\InventorySequenceRepository;
use App\Service\InventorySequenceService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class InventorySequenceServiceTest extends TestCase
{
    public function testNextSequenceIncrements(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(InventorySequenceRepository::class);
        $inventory = $this->createMock(Inventory::class);

        $sequence = new InventorySequence();

        $repo->method('findForUpdate')->willReturn($sequence);

        $em->method('wrapInTransaction')->willReturnCallback(function ($callback) {
            return $callback($this->createMock(EntityManagerInterface::class));
        });

        $service = new InventorySequenceService($em, $repo);

        $this->assertSame(1, $service->nextValue($inventory));
        $this->assertSame(2, $service->nextValue($inventory));
    }
}

// Бизнес-логика вынесена в сервис, тестируется без БД.
