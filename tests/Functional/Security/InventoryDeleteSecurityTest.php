<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\Inventory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Тест на безопасность удаления инвентаря.
 * Проверяем сложный кейс: можно ли удалить чужой инвентарь, если он публичный?
 * Спойлер: нет, удалять может только владелец.
 */
final class InventoryDeleteSecurityTest extends WebTestCase
{
    /**
     * Обычный пользователь не должен иметь возможности удалить чужой инвентарь,
     * даже если тот открыт для просмотра всем (isPublic = true).
     */
    public function testUserCannotDeleteForeignPublicInventory(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        // ---------- Готовим данные ----------

        $admin = new User();
        $admin->setEmail('admin_' . uniqid('', true) . '@example.com');
        $admin->setPassword('password');
        $admin->setRoles(['ROLE_ADMIN']);

        $user = new User();
        $user->setEmail('user_' . uniqid('', true) . '@example.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);

        $inventory = new Inventory();
        $inventory->setName('Admin Public Inventory');
        $inventory->setOwner($admin);
        $inventory->setIsPublic(true);

        $em->persist($admin);
        $em->persist($user);
        $em->persist($inventory);
        $em->flush();

        // ---------- Действие ----------

        // Логинимся под обычным пользователем (не владельцем)
        $client->loginUser($user);

        // Инициализируем сессию, чтобы Symfony могла работать с CSRF
        $client->request('GET', '/inventories');
        $container->get('request_stack')->push($client->getRequest());

        // Генерируем правильный CSRF-токен, чтобы тест упал именно на проверке прав (Voter),
        // а не на проверке токена.
        $csrfTokenManager = $container->get('security.csrf.token_manager');
        $csrfToken = $csrfTokenManager
            ->getToken('inventory_delete_' . $inventory->getId())
            ->getValue();

        // Пытаемся удалить чужой инвентарь
        $client->request(
            'POST',
            '/inventories/' . $inventory->getId() . '/delete',
            ['_token' => $csrfToken]
        );

        // ---------- Проверка ----------

        // Ожидаем "403 Forbidden"
        $this->assertResponseStatusCodeSame(403);

        // Проверяем в БД, что инвентарь никуда не делся
        $stillExists = $em->getRepository(Inventory::class)
            ->find($inventory->getId());

        $this->assertNotNull($stillExists);
    }
}
