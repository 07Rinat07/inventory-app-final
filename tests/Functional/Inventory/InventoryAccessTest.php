<?php

namespace App\Tests\Functional\Inventory;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Тесты на проверку прав доступа к инвентарю.
 * Убеждаемся, что «левые» люди не видят наши списки.
 */
final class InventoryAccessTest extends WebTestCase
{
    /**
     * Анонимного пользователя должно выкидывать на страницу логина.
     */
    public function testGuestCannotAccessInventory(): void
    {
        $client = static::createClient();
        $client->request('GET', '/inventories');

        $this->assertResponseRedirects('/login');
    }
}
