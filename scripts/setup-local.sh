#!/bin/bash
# One-command local bootstrap: builds/starts containers, installs WordPress,
# activates the theme/plugins, and seeds demo content.
set -euo pipefail
cd "$(dirname "$0")/.."

if [ ! -f .env ]; then
	echo "==> No .env found, copying .env.example (edit it, then re-run if you change secrets)"
	cp .env.example .env
fi

echo "==> Building and starting containers"
docker compose up -d --build

echo "==> Running WordPress bootstrap + seed"
docker compose run --rm --entrypoint bash wpcli /var/www/html/wp-cli-scripts/bootstrap.sh

set -a; source .env; set +a
echo ""
echo "======================================================"
echo " WagerWise is ready:"
echo "   Site:      ${WP_HOME}"
echo "   wp-admin:  ${WP_HOME}/wp-admin  (${WP_ADMIN_USER} / ${WP_ADMIN_PASSWORD})"
echo "   Mailhog:   http://localhost:8025"
echo "   phpMyAdmin http://localhost:8081"
echo "======================================================"
