#!/bin/sh
set -e

echo "Waiting for database..."
until pg_isready -h database -U app; do
  sleep 1
done

if [ "$APP_ENV" != "prod" ]; then
  echo "Running migrations ($APP_ENV)..."
  php bin/console doctrine:migrations:migrate --no-interaction

  echo "Loading fixtures ($APP_ENV)..."
  php bin/console doctrine:fixtures:load --no-interaction || true
fi

exec "$@"
ы
