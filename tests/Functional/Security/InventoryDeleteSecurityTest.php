<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\Inventory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class InventoryDeleteSecurityTest extends WebTestCase
{
    public function testUserCannotDeleteForeignPublicInventory(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        // ---------- arrange ----------

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

        // ---------- act ----------

        // логинимся под обычным пользователем
        $client->loginUser($user);

        // Инициализируем сессию и пушим запрос в стек, чтобы CsrfTokenManager её увидел
        $client->request('GET', '/inventories');
        $container->get('request_stack')->push($client->getRequest());

        // CSRF-токен
        $csrfTokenManager = $container->get('security.csrf.token_manager');
        $csrfToken = $csrfTokenManager
            ->getToken('inventory_delete_' . $inventory->getId())
            ->getValue();

        // попытка удалить чужой public inventory
        $client->request(
            'POST',
            '/inventories/' . $inventory->getId() . '/delete',
            ['_token' => $csrfToken]
        );

        // ---------- assert ----------

        $this->assertResponseStatusCodeSame(403);

        $stillExists = $em->getRepository(Inventory::class)
            ->find($inventory->getId());

        $this->assertNotNull($stillExists);
    }
}
