#!/usr/bin/env sh

# Criar diretórios necessários com permissões corretas
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/storage/app/private
mkdir -p /var/www/html/storage/media-library/temp

# Garantir que o usuário www-data tenha permissão de escrita (se executando como root)
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data /var/www/html/storage 2>/dev/null || true
    chmod -R 775 /var/www/html/storage 2>/dev/null || true
fi

# Criar diretório temporário do sistema se não existir
mkdir -p /tmp/media-library
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data /tmp/media-library 2>/dev/null || true
    chmod -R 775 /tmp/media-library 2>/dev/null || true
fi

if [ ! -d "/var/www/html/vendor" ]; then
    echo "📦 Instalando dependências do Composer..."
    composer install --no-interaction --prefer-dist
    echo "✅ Dependências do Composer instaladas!"
fi

if [ -n "$DB_HOST" ]; then
    echo "⏳ Aguardando banco de dados estar disponível..."
    until nc -z "$DB_HOST" "${DB_PORT:-5432}"; do
        sleep 1
    done
    echo "✅ Banco de dados está disponível!"
fi

echo "✅ Iniciando Laravel Octane com Swoole (modo desenvolvimento)..."
exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=80 --watch

