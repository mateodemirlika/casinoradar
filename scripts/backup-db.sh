#!/bin/bash
# Dumps the database and tars the uploads directory, timestamped, into ./backups/.
# Works against whichever stack is currently running (local or prod compose).
set -euo pipefail
cd "$(dirname "$0")/.."

# Without this, plain `docker compose run` resolves to the LOCAL wpcli
# service (docker-compose.yml + any docker-compose.override.yml), which
# bind-mounts ./wp-content from the git checkout — no uploads/ there, it's
# gitignored. Production's real uploads live in the wp_content_prod named
# volume, only mounted by the wpcli service defined in
# docker-compose.prod.yml, so that file must be explicitly included here too
# (matching how deploy-prod.sh always does), or the archive step silently
# reads from the wrong place entirely (confirmed: "tar: uploads: Cannot stat"
# — the DB export above is unaffected since the `db` service/db_data volume
# is identical either way, but this uploads step is not).
if [ -f .env ] && grep -q '^WP_ENV=production' .env; then
	COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"
else
	COMPOSE="docker compose"
fi

STAMP=$(date +%Y%m%d-%H%M%S)
mkdir -p backups

echo "==> Exporting database"
$COMPOSE run --rm --entrypoint bash wpcli -c \
	"wp --allow-root --path=/var/www/html db export - " > "backups/db-${STAMP}.sql"

echo "==> Archiving uploads"
$COMPOSE run --rm --entrypoint bash wpcli -c \
	"tar -czf - -C /var/www/html/wp-content uploads" > "backups/uploads-${STAMP}.tar.gz"

echo "==> Done: backups/db-${STAMP}.sql, backups/uploads-${STAMP}.tar.gz"
