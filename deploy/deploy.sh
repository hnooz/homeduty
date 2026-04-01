#!/usr/bin/env bash
# =============================================================================
# HomeDuty — Deploy Script
# Run on the server after each code push.
#
# Usage:
#   ./deploy.sh              # Normal deploy
#   ./deploy.sh --first-run  # First deploy (runs migrations fresh, builds assets)
# =============================================================================
set -euo pipefail

APP_DIR="/var/www/homeduty"
APP_USER="homeduty"
WORKER_SERVICE="homeduty-worker"

FIRST_RUN=false
if [[ "${1:-}" == "--first-run" ]]; then
  FIRST_RUN=true
fi

cd "${APP_DIR}"

echo "→ Putting application in maintenance mode..."
php artisan down --retry=10

echo "→ Pulling latest code..."
git pull origin main

echo "→ Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "→ Installing Node dependencies and building assets..."
npm ci --prefer-offline
npm run build

echo "→ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "→ Running database migrations..."
if [[ "$FIRST_RUN" == true ]]; then
  php artisan migrate --force --no-interaction
  php artisan db:seed --force --no-interaction
  php artisan storage:link
else
  php artisan migrate --force --no-interaction
fi

echo "→ Clearing stale caches..."
php artisan queue:restart

echo "→ Restarting queue worker..."
systemctl restart "${WORKER_SERVICE}" || true

echo "→ Reloading PHP-FPM..."
systemctl reload php8.3-fpm

echo "→ Taking application out of maintenance mode..."
php artisan up

echo ""
echo "✓ Deploy complete."
