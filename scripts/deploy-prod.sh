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

# `up -d` only recreates wordpress when its image changes, so nginx can be
# left holding the old wordpress container's IP (fastcgi_pass resolves once
# per worker process lifetime) -> 502s until nginx happens to restart. Always
# restart it here so it re-resolves, and so it picks up any nginx config
# template changes shipped in the same deploy.
echo "==> Restarting nginx"
$COMPOSE restart nginx

echo "==> Deploy complete."
