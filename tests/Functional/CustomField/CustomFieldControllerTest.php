<?php

namespace App\Tests\Functional\CustomField;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CustomFieldControllerTest extends WebTestCase
{
    public function testFieldsPageRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/inventories/1/fields');

        $this->assertResponseRedirects('/login');
    }
}
