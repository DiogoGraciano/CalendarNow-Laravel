#!/usr/bin/env sh

# Executa migrações do banco central
php artisan migrate --force || true

# Executa migrações de todos os tenants
echo "🔄 Executando migrações dos tenants..."
php artisan tenants:migrate --force || true

# Executa seeders de permissões em todos os tenants
echo "🌱 Executando seeders de permissões nos tenants..."
php artisan tenants:seed --class=PermissionSeeder --force || true

# Cria link simbólico para storage
if [ ! -L "/var/www/public/storage" ]; then
    php artisan storage:link || true
fi

# Garante que o arquivo hot não existe (força uso de assets compilados)
rm -f /var/www/public/hot

# Verifica se o manifest.json existe (assets compilados)
if [ ! -f "/var/www/public/build/.vite/manifest.json" ] && [ ! -f "/var/www/public/build/manifest.json" ]; then
    echo "⚠️  AVISO: manifest.json não encontrado. Os assets podem não estar compilados."
fi

# Limpa e otimiza o cache da aplicação
php artisan optimize:clear || true
php artisan optimize || true

# Inicia Laravel Octane com Swoole
echo "✅ Iniciando Laravel Octane com Swoole..."
exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=80