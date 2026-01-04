<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\CustomField\CustomFieldType;
use App\Entity\CustomField;
use App\Entity\Inventory;
use App\Entity\InventoryAccess;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * AppFixtures
 *
 * Цель:
 * - Поднять базовые данные для dev/test окружений:
 *   - admin + user
 *   - несколько inventories
 *   - ACL (InventoryAccess)
 *   - custom fields (2-4 штуки)
 *
 * Это удобно:
 * - для ручной проверки функционала
 * - для демо ментору
 * - для функциональных тестов
 */
final class AppFixtures extends Fixture
{
    // Явно фиксируем логины/пароли, чтобы не искать в базе.
    private const ADMIN_EMAIL = 'admin@test.com';
    private const ADMIN_PASSWORD = 'admin12345';

    private const USER_EMAIL = 'user@test.com';
    private const USER_PASSWORD = 'user12345';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        /**
         * 1) Пользователи
         * ----------------
         * roles:
         * - admin: ROLE_ADMIN (ROLE_USER добавится автоматически через getRoles())
         * - user:  пусто (ROLE_USER тоже добавится автоматически)
         */
        $admin = (new User())
            ->setEmail(self::ADMIN_EMAIL)
            ->setRoles(['ROLE_ADMIN'])
            ->setTheme('light')
            ->setLocale('en');

        $admin->setPassword($this->passwordHasher->hashPassword($admin, self::ADMIN_PASSWORD));

        $user = (new User())
            ->setEmail(self::USER_EMAIL)
            ->setRoles([]) // ROLE_USER будет добавлен в getRoles()
            ->setTheme('light')
            ->setLocale('ru');

        $user->setPassword($this->passwordHasher->hashPassword($user, self::USER_PASSWORD));

        $manager->persist($admin);
        $manager->persist($user);

        /**
         * 2) Inventories
         * -------------
         * Делаем 3 штуки:
         * - admin private (не публичный)
         * - admin public (публичный)
         * - user private (не публичный)
         *
         *   проверить:
         * - "свои" инвентари
         * - доступ к public
         * - доступ по ACL
         */
        $invAdminPrivate = (new Inventory())
            ->setOwner($admin)
            ->setName('Admin Private Inventory')
            ->setIsPublic(false);

        $invAdminPublic = (new Inventory())
            ->setOwner($admin)
            ->setName('Admin Public Inventory')
            ->setIsPublic(true);

        $invUserPrivate = (new Inventory())
            ->setOwner($user)
            ->setName('User Private Inventory')
            ->setIsPublic(false);

        $manager->persist($invAdminPrivate);
        $manager->persist($invAdminPublic);
        $manager->persist($invUserPrivate);

        /**
         * 3) ACL (InventoryAccess)
         * ------------------------
         * Делаем user доступ к admin private:
         * - WRITE (чтобы проверить EDIT / MANAGE_FIELDS / DELETE если у тебя так в Voter)
         *
         * При этом:
         * - admin по Voter'у и так "суперюзер" (если у тебя включён блок ROLE_ADMIN => true)
         * - user увидит admin private через InventoryRepository (join с InventoryAccess)
         */
        $accessUserToAdminPrivate = (new InventoryAccess())
            ->setInventory($invAdminPrivate)
            ->setUser($user)
            ->setPermission(InventoryAccess::PERMISSION_WRITE);

        $manager->persist($accessUserToAdminPrivate);

        /**
         * 4) Custom fields
         * ---------------
         * Для проверки “до 3 полей каждого типа” и работы UI.
         * Позиции начинаем с 0 и увеличиваем.
         *
         * Сделаем:
         * - на invAdminPrivate: TEXT(required), DATE(optional)
         * - на invUserPrivate: NUMBER(required), BOOLEAN(optional)
         */
        $cf1 = new CustomField($invAdminPrivate, CustomFieldType::TEXT, 0);
        $cf1->setIsRequired(true);

        $cf2 = new CustomField($invAdminPrivate, CustomFieldType::DATE, 1);
        $cf2->setIsRequired(false);

        $cf3 = new CustomField($invUserPrivate, CustomFieldType::NUMBER, 0);
        $cf3->setIsRequired(true);

        $cf4 = new CustomField($invUserPrivate, CustomFieldType::BOOLEAN, 1);
        $cf4->setIsRequired(false);

        $manager->persist($cf1);
        $manager->persist($cf2);
        $manager->persist($cf3);
        $manager->persist($cf4);

        /**
         * Финальный flush:
         * Важно делать один flush в конце — быстрее и чище.
         */
        $manager->flush();
    }
}
