#!/bin/sh
set -e

# Ждем пока БД станет доступна
until nc -z database 5432; do
  echo "Waiting for database to be ready..."
  sleep 1
done

# Выполнение миграций
php bin/console doctrine:migrations:migrate --no-interaction

# Загрузка фикстур (только если БД пустая или по желанию)
# В данном случае загружаем для демонстрации
php bin/console doctrine:fixtures:load --no-interaction

exec "$@"
