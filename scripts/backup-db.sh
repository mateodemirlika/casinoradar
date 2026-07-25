#!/bin/bash
# Dumps the database and tars the uploads directory, timestamped, into ./backups/.
# Works against whichever stack is currently running (local or prod compose).
set -euo pipefail
cd "$(dirname "$0")/.."

STAMP=$(date +%Y%m%d-%H%M%S)
mkdir -p backups

echo "==> Exporting database"
docker compose run --rm --entrypoint bash wpcli -c \
	"wp --allow-root --path=/var/www/html db export - " > "backups/db-${STAMP}.sql"

echo "==> Archiving uploads"
docker compose run --rm --entrypoint bash wpcli -c \
	"tar -czf - -C /var/www/html/wp-content uploads" > "backups/uploads-${STAMP}.tar.gz"

echo "==> Done: backups/db-${STAMP}.sql, backups/uploads-${STAMP}.tar.gz"
