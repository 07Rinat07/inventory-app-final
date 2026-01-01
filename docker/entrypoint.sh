#!/bin/sh
set -e

echo "Waiting for database..."
until pg_isready -h database -U app; do
  sleep 1
done

if [ "$APP_ENV" != "prod" ]; then
  echo "Checking database..."
  if [ "$APP_ENV" = "test" ]; then
    PGPASSWORD=$POSTGRES_PASSWORD psql -h database -U app -d postgres -tc "SELECT 1 FROM pg_database WHERE datname = 'app_test'" | grep -q 1 || \
    PGPASSWORD=$POSTGRES_PASSWORD psql -h database -U app -d postgres -c "CREATE DATABASE app_test;"
  fi

  echo "Running migrations ($APP_ENV)..."
  php bin/console doctrine:migrations:migrate --no-interaction

  echo "Loading fixtures ($APP_ENV)..."
  php bin/console doctrine:fixtures:load --no-interaction || true
fi

exec "$@"
