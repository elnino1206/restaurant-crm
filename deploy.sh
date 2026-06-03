#!/bin/bash
set -e

echo "🚀 Deploying Restaurant CRM..."

# Цвета
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log() { echo -e "${GREEN}[$(date '+%H:%M:%S')]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

# Проверка .env
if [ ! -f ".env" ]; then
    error ".env not found! Copy .env.production.example to .env and fill it in."
fi

# 1. Pull последних изменений
log "Pulling latest code..."
git pull origin main

# 2. Сборка продакшн образа
log "Building production Docker image..."
DOCKER_BUILDKIT=0 docker compose -f docker-compose.prod.yml build --no-cache php

# 3. Запуск/перезапуск контейнеров (без downtime)
log "Starting containers..."
docker compose -f docker-compose.prod.yml up -d --remove-orphans

# 4. Ждём что php-fpm готов
log "Waiting for PHP-FPM..."
sleep 5

# 5. Миграции
log "Running migrations..."
docker compose -f docker-compose.prod.yml exec php php artisan migrate --force

# 6. Очистка кэша
log "Clearing and warming up cache..."
docker compose -f docker-compose.prod.yml exec php php artisan config:cache
docker compose -f docker-compose.prod.yml exec php php artisan route:cache
docker compose -f docker-compose.prod.yml exec php php artisan view:cache

# 7. Права на storage
log "Setting storage permissions..."
docker compose -f docker-compose.prod.yml exec php chown -R www-data:www-data storage bootstrap/cache

# 8. Регистрация Telegram webhook'ов
log "Registering Telegram webhooks..."
docker compose -f docker-compose.prod.yml exec php php artisan telegram:webhook:register || warn "Webhook registration skipped (no bots configured)"

log "✅ Deploy complete!"
docker compose -f docker-compose.prod.yml ps
