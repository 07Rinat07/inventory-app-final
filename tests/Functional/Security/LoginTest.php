<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Тестируем форму входа и редиректы.
 * Базовая проверка безопасности приложения.
 */
final class LoginTest extends WebTestCase
{
    /**
     * Гость должен видеть страницу входа с нужными инпутами.
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
     * Если не залогинился — в личный кабинет не пустят.
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/inventories');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Проверяем, что реальный пользователь может зайти (данные из фикстур).
     */
    public function testUserCanLogin(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        // Ищем кнопку и заполняем форму
        $form = $crawler->selectButton('Login')->form([
            '_username' => 'user@test.com',
            '_password' => 'user12345',
        ]);

        $client->submit($form);

        // После входа — сразу в список инвентарей
        $this->assertResponseRedirects('/inventories');
        $client->followRedirect();

        $this->assertSelectorExists('nav');
    }

    /**
     * Админ тоже должен уметь входить без проблем.
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
