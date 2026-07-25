#!/bin/bash
set -euo pipefail

# Sync the image's baked-in wp-content into the live, volume-backed path on
# every container start, so code changes ship on redeploy while uploads
# (persisted in the same volume) are never touched.
rsync -a --delete --exclude 'uploads' /usr/src/wp-content-dist/ /var/www/html/wp-content/
mkdir -p /var/www/html/wp-content/uploads
chown -R www-data:www-data /var/www/html/wp-content

exec docker-entrypoint.sh "$@"
