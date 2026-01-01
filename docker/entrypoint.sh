#!/bin/sh
set -e

echo "Waiting for database to be ready..."

until pg_isready -h database -U app > /dev/null 2>&1; do
  sleep 1
done

echo "Database is ready."

if [ "$APP_ENV" != "prod" ]; then
    echo "Ensuring databases exist..."
    # Создаем основную базу, если она не создана (хотя postgres image обычно создает ее по POSTGRES_DB)
    # Но для надежности и для тестовой базы:
    PGPASSWORD=$POSTGRES_PASSWORD psql -h database -U app -d postgres -tc "SELECT 1 FROM pg_database WHERE datname = 'app_test'" | grep -q 1 || \
    PGPASSWORD=$POSTGRES_PASSWORD psql -h database -U app -d postgres -c "CREATE DATABASE app_test;"

    echo "Installing composer dependencies..."
    composer install --no-interaction --optimize-autoloader

    echo "Running migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction

    echo "Loading fixtures..."
    php bin/console doctrine:fixtures:load --no-interaction

    echo "Setting up test database..."
    php bin/console doctrine:migrations:migrate --no-interaction --env=test
fi

exec "$@"
