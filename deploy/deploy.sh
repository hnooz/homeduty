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

git config --global --add safe.directory "${APP_DIR}"

cd "${APP_DIR}"

echo "→ Pulling latest code..."
if [[ "$FIRST_RUN" == false ]]; then
  git pull origin main
fi

echo "→ Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

if [[ "$FIRST_RUN" == true ]]; then
  echo "→ Generating application key..."
  php artisan key:generate --force
fi

echo "→ Clearing stale caches before build..."
php artisan optimize:clear

echo "→ Installing Node dependencies and building assets..."
npm ci --prefer-offline
NODE_OPTIONS="--max-old-space-size=512" npm run build

if [[ "$FIRST_RUN" == false ]]; then
  echo "→ Putting application in maintenance mode..."
  php artisan down --retry=10
fi

echo "→ Caching configuration..."
php artisan optimize

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
sudo systemctl restart "${WORKER_SERVICE}" || true

echo "→ Reloading PHP-FPM..."
sudo systemctl reload php8.3-fpm

echo "→ Taking application out of maintenance mode..."
php artisan up

echo ""
echo "✓ Deploy complete."
