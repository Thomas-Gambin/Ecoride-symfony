#!/bin/sh
set -e
cd /var/www/html

DOCKER_DB_URL=""
if [ -f /.dockerenv ]; then
  PG_VER="${POSTGRES_VERSION:-16}"
  PG_CS="${POSTGRES_CHARSET:-utf8}"
  DOCKER_DB_URL="postgresql://${POSTGRES_USER:-ecoride}:${POSTGRES_PASSWORD:-ecoride}@database:5432/${POSTGRES_DB:-ecoride}?serverVersion=${PG_VER}&charset=${PG_CS}"
  export DATABASE_URL="$DOCKER_DB_URL"
fi

if command -v git >/dev/null 2>&1; then
  git config --global --add safe.directory /var/www/html >/dev/null 2>&1 || true
fi

if [ -f composer.json ]; then
  composer install --no-interaction --prefer-dist --no-progress
fi
mkdir -p var/cache var/log
chown -R www-data:www-data var
if [ -d vendor ]; then
  chown -R www-data:www-data vendor
fi

if [ -n "$DOCKER_DB_URL" ]; then
  exec su -p -s /bin/sh www-data -c "cd /var/www/html && DATABASE_URL=\"${DOCKER_DB_URL}\" exec php -d variables_order=EGPCS -S 0.0.0.0:8000 -t public public/router.php"
else
  exec su -p -s /bin/sh www-data -c 'cd /var/www/html && exec php -d variables_order=EGPCS -S 0.0.0.0:8000 -t public public/router.php'
fi
