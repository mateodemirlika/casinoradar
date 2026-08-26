#!/bin/bash
set -euo pipefail

# Sync the image's baked-in wp-content into the live, volume-backed path on
# every container start, so code changes ship on redeploy while uploads
# (persisted in the same volume) are never touched.
#
# Also excludes polylang/, seo-by-rank-math/, and redis-cache/ (plugins
# deliberately NOT committed to the repo, see .gitignore — installed at
# runtime by bootstrap.sh via `wp plugin install`) and object-cache.php (the
# redis-cache drop-in, created at runtime by `wp redis enable`, living
# outside plugins/). None of these ever exist in /usr/src/wp-content-dist/,
# so without these excludes --delete treats "not in the freshly built image"
# as "delete it", wiping out every runtime-installed plugin/drop-in on the
# very next rebuild after they were installed — which is exactly what
# happened here once.
rsync -a --delete \
	--exclude 'uploads' \
	--exclude 'plugins/polylang' \
	--exclude 'plugins/seo-by-rank-math' \
	--exclude 'plugins/redis-cache' \
	--exclude 'object-cache.php' \
	/usr/src/wp-content-dist/ /var/www/html/wp-content/
mkdir -p /var/www/html/wp-content/uploads
chown -R www-data:www-data /var/www/html/wp-content

# Same story for the standalone /cr and /rftr redirect endpoints (outside
# wp-content, no uploads to preserve there).
rsync -a --delete /usr/src/cr-dist/ /var/www/html/cr/
rsync -a --delete /usr/src/rftr-dist/ /var/www/html/rftr/
chown -R www-data:www-data /var/www/html/cr /var/www/html/rftr

exec docker-entrypoint.sh "$@"
