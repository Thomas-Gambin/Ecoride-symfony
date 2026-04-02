#!/bin/sh
set -e
cd /var/www/html
if [ -f composer.json ]; then
  composer install --no-interaction --prefer-dist --no-progress
fi
mkdir -p var/cache var/log
chown -R www-data:www-data var
if [ -d vendor ]; then
  chown -R www-data:www-data vendor
fi
exec runuser -u www-data -- php -S 0.0.0.0:8000 -t public public/router.php
