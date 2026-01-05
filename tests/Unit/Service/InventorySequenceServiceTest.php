<?php

namespace App\Tests\Unit\Service;

use App\Entity\Inventory;
use App\Entity\InventorySequence;
use App\Repository\InventorySequenceRepository;
use App\Service\InventorySequenceService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Мок-тест для сервиса последовательностей.
 * Проверяем логику инкремента без реального обращения к базе данных.
 */
final class InventorySequenceServiceTest extends TestCase
{
    /**
     * Проверяем, что при вызове nextValue значение счетчика действительно растет.
     */
    public function testNextSequenceIncrements(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(InventorySequenceRepository::class);
        $inventory = $this->createMock(Inventory::class);

        $sequence = new InventorySequence();

        $repo->method('findForUpdate')->willReturn($sequence);

        // Имитируем выполнение транзакции
        $em->method('wrapInTransaction')->willReturnCallback(function ($callback) {
            return $callback($this->createMock(EntityManagerInterface::class));
        });

        $service = new InventorySequenceService($em, $repo);

        $this->assertSame(1, $service->nextValue($inventory));
        $this->assertSame(2, $service->nextValue($inventory));
    }
}
