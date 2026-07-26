#!/bin/bash
# Builds the immutable production image and (re)starts the prod stack.
# Assumes: .env is filled in with real production values, DNS for $DOMAIN
# already points at this host, and the shared Traefik reverse proxy (external
# "proxy" Docker network) is already running on this host — it discovers this
# site via the nginx service's labels and handles TLS/Let's Encrypt itself,
# so there is no certbot/cert bootstrap step here.
set -euo pipefail
cd "$(dirname "$0")/.."

COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

echo "==> Building production image"
$COMPOSE build wordpress

echo "==> Starting/updating the stack"
$COMPOSE up -d

echo "==> Running WordPress bootstrap (idempotent) + seed"
$COMPOSE run --rm --entrypoint bash wpcli /var/www/html/wp-cli-scripts/bootstrap.sh

echo "==> Flushing caches"
$COMPOSE run --rm wpcli cache flush
$COMPOSE run --rm wpcli rewrite flush --hard

echo "==> Deploy complete."
