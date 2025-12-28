## 👤 Автор

## Rinat Sarmuldin. email--> [ura07srr@gmail.com](mailto:ura07srr@gmail.com)

---

<div align="center">
  <img src="https://media.giphy.com/media/dWesBcTLavkZuG35MI/giphy.gif" width="600" height="300"/>
</div>

---

## Inventory App - final ver — проект for Itransition (Symfony).

#### AppFixtures
- Явно фиксируем логины/пароли, чтобы не искать в базе.
- admin@test.com (ROLE_ADMIN)
- user@test.com (обычный)
- noaccess@test.com (обычный, без ACL; видит только public)
#### 1) Установи FixturesBundle (dev/test)
Выполни в терминале:
composer require --dev doctrine/doctrine-fixtures-bundle

Symfony Flex сам подключит бандл в config/bundles.php (обычно в dev/test).
Проверь, что команда появилась:

php bin/console list doctrine | grep fixtures

Должно быть doctrine:fixtures:load.


2) Готовые фикстуры: src/DataFixtures/AppFixtures.php

Создай файл src/DataFixtures/AppFixtures.php:

/**
 * в итоге

После загрузки фикстур будет:

admin@test.com
 / admin12345 (ROLE_ADMIN)

user@test.com
 / user12345 (ROLE_USER)

Inventories:

Admin Private Inventory (owner=admin, public=false)

Admin Public Inventory (owner=admin, public=true)

User Private Inventory (owner=user, public=false)

ACL:

user получает WRITE на Admin Private Inventory (проверка edit/manage-fields/delete)

Custom fields:

на Admin Private Inventory: TEXT(required), DATE(optional)

на User Private Inventory: NUMBER(required), BOOLEAN(optional)

3) Как загрузить фикстуры в DEV

В dev (обычная база):
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
php bin/console cache:clear

Проверка SQL:
php bin/console doctrine:query:sql "SELECT id, email, roles FROM users ORDER BY id;"
php bin/console doctrine:query:sql "SELECT id, name, is_public, owner_id FROM inventories ORDER BY id;"
php bin/console doctrine:query:sql "SELECT inventory_id, user_id, permission FROM inventory_access ORDER BY id;"
php bin/console doctrine:query:sql "SELECT id, inventory_id, type, position, is_required FROM custom_fields ORDER BY inventory_id, position;"

4) Как загрузить фикстуры в TEST (для PHPUnit)

Важно: тесты лучше гонять в отдельной БД (APP_ENV=test).
Обычно workflow такой:

php bin/console doctrine:database:drop --env=test --force --if-exists
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --no-interaction
php bin/phpunit

Важный момент с миграциями и sequences (inventories_id_seq уже есть)

Фикстуры будут работать только если схема/миграции синхронизированы.
Если у тебя сейчас “diff” пытается создать то, что уже существует, значит БД уже частично вручную/старыми миграциями создана.

Самый чистый способ привести dev-базу в порядок:
php bin/console doctrine:database:drop --force --if-exists
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction

