#!/bin/bash
# One-time production bootstrap: obtains the first Let's Encrypt certificate.
# Run this once on the VPS after `docker compose -f docker-compose.yml \
# -f docker-compose.prod.yml up -d db wordpress redis`, before nginx has ever
# started with the real (SSL-requiring) config. Re-running is safe (certbot
# no-ops if a valid cert already exists).
set -euo pipefail
cd "$(dirname "$0")/.."
set -a; source .env; set +a

COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

echo "==> Starting nginx with the HTTP-only bootstrap config"
$COMPOSE run --rm -d --name wagerwise_nginx_bootstrap \
  -p 80:80 \
  -v "$(pwd)/nginx/prod/nginx.conf:/etc/nginx/nginx.conf:ro" \
  -v "$(pwd)/nginx/prod/templates-bootstrap:/etc/nginx/templates:ro" \
  -v certbot_www:/var/www/certbot \
  -e DOMAIN="$DOMAIN" \
  nginx nginx:1.27-alpine >/dev/null

sleep 2

echo "==> Requesting certificate for $DOMAIN"
$COMPOSE run --rm certbot certonly --webroot -w /var/www/certbot \
  -d "$DOMAIN" --email "$CERTBOT_EMAIL" --agree-tos --non-interactive

echo "==> Stopping bootstrap nginx"
docker stop wagerwise_nginx_bootstrap >/dev/null

echo "==> Starting full stack with the real SSL config"
$COMPOSE up -d

echo "Done. https://$DOMAIN should now be serving over TLS."
