#!/usr/bin/env sh
set -e

cd /var/www

# pastas obrigatorias
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache

# permissões
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# se nao existir .env, cria automaticamente a partir do example
if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

# gera APP_KEY se estiver vazio
if [ -f .env ] && ! grep -q "^APP_KEY=base64:" .env; then
  php artisan key:generate --force || true
fi

php artisan optimize:clear || true

exec "$@"
