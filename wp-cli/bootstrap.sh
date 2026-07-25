#!/bin/bash
# Idempotent: safe to re-run. Installs WordPress core (if not already),
# activates the theme + required plugins, and sets permalinks.
set -euo pipefail

WP="wp --allow-root --path=/var/www/html"

echo "==> Waiting for the database"
until $WP db check --quiet 2>/dev/null; do
	sleep 2
done

if ! $WP core is-installed --quiet 2>/dev/null; then
	echo "==> Installing WordPress core"
	$WP core install \
		--url="${WP_HOME}" \
		--title="${WP_TITLE:-WagerWise}" \
		--admin_user="${WP_ADMIN_USER}" \
		--admin_password="${WP_ADMIN_PASSWORD}" \
		--admin_email="${WP_ADMIN_EMAIL}" \
		--skip-email
else
	echo "==> WordPress core already installed, skipping"
fi

echo "==> Activating theme"
$WP theme activate wagerwise

echo "==> Activating bundled plugins"
$WP plugin activate wagerwise-core

echo "==> Installing required plugins"
$WP plugin install polylang --activate || $WP plugin activate polylang
$WP plugin install seo-by-rank-math --activate || $WP plugin activate seo-by-rank-math

if [ "${WP_ENV:-local}" = "production" ]; then
	$WP plugin install redis-cache --activate || $WP plugin activate redis-cache
	$WP redis enable || true
fi

echo "==> Setting permalink structure"
$WP rewrite structure '/%postname%/' --hard
$WP rewrite flush --hard

echo "==> Seeding demo content"
$WP eval-file /var/www/html/wp-cli-scripts/seed.php

echo "==> Bootstrap complete."
echo "    Site:     ${WP_HOME}"
echo "    wp-admin: ${WP_HOME}/wp-admin  (user: ${WP_ADMIN_USER})"
