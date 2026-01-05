<?php

namespace App\Tests\Functional\CustomField;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Тестируем управление кастомными полями через контроллер.
 */
final class CustomFieldControllerTest extends WebTestCase
{
    /**
     * Нельзя просто так зайти и начать править поля без авторизации.
     */
    public function testFieldsPageRequiresAuth(): void
    {
        $client = static::createClient();
        // Пытаемся зайти на страницу полей какого-то инвентаря
        $client->request('GET', '/inventories/1/fields');

        // Должно перекинуть на логин
        $this->assertResponseRedirects('/login');
    }
}
