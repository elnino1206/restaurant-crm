#!/bin/bash
# Запускать от root на свежем Ubuntu 22.04/24.04
set -e

DOMAIN=$1
GIT_REPO=$2  # например: git@github.com:your/repo.git
APP_DIR="/var/www/restaurant-crm"

if [ -z "$DOMAIN" ] || [ -z "$GIT_REPO" ]; then
    echo "Usage: ./server-setup.sh YOUR_DOMAIN git@github.com:user/repo.git"
    exit 1
fi

echo "=== Installing Docker ==="
apt-get update -q
apt-get install -y curl git ufw

curl -fsSL https://get.docker.com | sh
usermod -aG docker $SUDO_USER 2>/dev/null || true

echo "=== Configuring Firewall ==="
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

echo "=== Cloning repository ==="
mkdir -p $APP_DIR
git clone $GIT_REPO $APP_DIR
cd $APP_DIR

echo "=== Setting up .env ==="
cp .env.production.example .env
echo ""
echo "⚠️  Edit .env before continuing:"
echo "   nano $APP_DIR/.env"
echo ""
echo "   Required fields:"
echo "   - APP_KEY (run: docker run --rm php:8.4-cli php -r \"echo base64_encode(random_bytes(32));\")"
echo "   - APP_URL=https://$DOMAIN"
echo "   - DB_PASSWORD"
echo "   - REDIS_PASSWORD"
echo "   - TELEGRAM_BOT_TOKEN"
echo ""
read -p "Press Enter when .env is ready..."

echo "=== Updating Nginx domain config ==="
sed -i "s/YOUR_DOMAIN/$DOMAIN/g" docker/nginx/prod.conf

echo "=== Starting containers (without SSL first) ==="
# Временный nginx без SSL для получения сертификата
docker compose -f docker-compose.prod.yml up -d postgres redis
sleep 5

echo "=== Obtaining SSL certificate ==="
docker run --rm \
    -v "$(pwd)/docker/nginx/ssl:/etc/letsencrypt" \
    -v "/var/www/certbot:/var/www/certbot" \
    -p 80:80 \
    certbot/certbot certonly \
    --standalone \
    --agree-tos \
    --no-eff-email \
    -d $DOMAIN \
    -m "admin@$DOMAIN"

echo "=== Full deploy ==="
docker compose -f docker-compose.prod.yml up -d --build

sleep 10

docker compose -f docker-compose.prod.yml exec php php artisan migrate --force
docker compose -f docker-compose.prod.yml exec php php artisan config:cache
docker compose -f docker-compose.prod.yml exec php php artisan route:cache
docker compose -f docker-compose.prod.yml exec php php artisan storage:link
docker compose -f docker-compose.prod.yml exec php chown -R www-data:www-data storage bootstrap/cache

echo ""
echo "✅ Server setup complete!"
echo "   Site: https://$DOMAIN"
echo ""
echo "Next steps:"
echo "  1. Create admin user: docker compose -f docker-compose.prod.yml exec php php artisan tinker"
echo "  2. Register Telegram webhook: docker compose -f docker-compose.prod.yml exec php php artisan telegram:webhook:register"
echo "  3. Setup cron (auto-renew SSL + scheduler):"
echo "     crontab -e"
echo "     Add: * * * * * cd $APP_DIR && docker compose -f docker-compose.prod.yml exec php php artisan schedule:run >> /dev/null 2>&1"
