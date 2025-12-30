#!/bin/sh
set -e

# Ждем пока БД станет доступна
echo "Waiting for database to be ready..."
until pg_isready -h database -U app; do
  sleep 1
done

# Выполнение миграций
echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Загрузка фикстур
echo "Loading fixtures..."
php bin/console doctrine:fixtures:load --no-interaction

# Подготовка тестовой базы данных
echo "Preparing test database..."
APP_ENV=test php bin/console doctrine:database:create --if-not-exists
APP_ENV=test php bin/console doctrine:migrations:migrate --no-interaction

exec "$@"
