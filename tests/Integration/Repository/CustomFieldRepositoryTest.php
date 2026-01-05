<?php

namespace App\Tests\Integration\Repository;

use App\Entity\CustomField;
use App\Entity\Inventory;
use App\Entity\User;
use App\Domain\CustomField\CustomFieldType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Интеграционный тест репозитория кастомных полей.
 * Проверяем правильность выборки данных из БД с учетом сортировки.
 */
final class CustomFieldRepositoryTest extends KernelTestCase
{
    /**
     * Поля должны возвращаться в порядке, заданном свойством position.
     */
    public function testFindByInventoryOrdered(): void
    {
        self::bootKernel();
        $em = static::getContainer()->get('doctrine')->getManager();

        // Создаем тестовые данные: юзер -> инвентарь -> пара полей
        $user = new User();
        $user->setEmail('test_' . uniqid() . '@example.com');
        $user->setPassword('password');
        $em->persist($user);

        $inventory = new Inventory();
        $inventory->setName('Test Inventory');
        $inventory->setOwner($user);
        $em->persist($inventory);

        // Поле с позицией 1 создаем раньше, чем с позицией 0,
        // чтобы проверить именно сортировку в SQL
        $field1 = new CustomField($inventory, CustomFieldType::TEXT, 1);
        $field2 = new CustomField($inventory, CustomFieldType::DATE, 0);

        $em->persist($field1);
        $em->persist($field2);
        $em->flush();

        $repo = $em->getRepository(CustomField::class);
        $fields = $repo->findByInventoryOrdered($inventory);

        // Проверяем, что поле с position=0 идет первым
        $this->assertSame(0, $fields[0]->getPosition());
        $this->assertSame(1, $fields[1]->getPosition());
    }
}
