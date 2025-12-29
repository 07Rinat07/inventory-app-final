<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LoginTest extends WebTestCase
{
    /**
     * Проверяем, что страница логина доступна гостю
     */
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"], input[name="email"]');
        $this->assertSelectorExists('input[name="_password"], input[name="password"]');
    }

    /**
     * Проверяем, что гость не может зайти в защищённый маршрут
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/inventories');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Проверяем успешный логин обычного пользователя
     * (использует данные из AppFixtures)
     */
    public function testUserCanLogin(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form([
            '_username' => 'user@test.com',
            '_password' => 'user12345',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/inventories');
        $client->followRedirect();

        $this->assertSelectorExists('nav');
    }

    /**
     * Проверяем, что админ тоже логинится
     */
    public function testAdminCanLogin(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form([
            '_username' => 'admin@test.com',
            '_password' => 'admin12345',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/inventories');
    }
}
