<?php

namespace App\Tests\Functional\Inventory;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class InventoryAccessTest extends WebTestCase
{
    public function testGuestCannotAccessInventory(): void
    {
        $client = static::createClient();
        $client->request('GET', '/inventories');

        $this->assertResponseRedirects('/login');
    }
}
