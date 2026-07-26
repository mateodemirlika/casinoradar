# WagerWise

A modern, casino.guru-inspired casino affiliate site built on WordPress:
casino reviews, bonus/deal listings, game info, and a blog — everything
editable from wp-admin, multilingual out of the box (Polylang), and
dockerized for both local development and production.

## Stack

- **WordPress** (PHP 8.3-FPM) + **MariaDB** + **Nginx**, all in Docker.
- **Theme**: `wagerwise` — a custom Full Site Editing (block) theme. No page
  builder.
- **Plugin**: `wagerwise-core` — custom post types (Casino, Bonus, Game),
  taxonomies, meta fields, a global settings page, and 12 custom Gutenberg
  blocks. Deliberately **no ACF dependency** (free or paid) — everything is
  built on native WordPress APIs, so there's zero licensing cost.
- **Multilingual**: Polylang (free). Seeded with English (default), Spanish,
  German, French, Portuguese, and Italian — add more anytime from
  **Languages → Languages** in wp-admin.
- **SEO**: Rank Math (free tier).

## Local development

1. Copy the environment file and adjust anything you like (defaults work
   out of the box for local use):
   ```bash
   cp .env.example .env
   ```
2. Run the one-command setup:
   ```bash
   ./scripts/setup-local.sh
   ```
   This builds the containers, installs WordPress, activates the theme and
   plugins, sets permalinks, and seeds demo content (5 casinos, 6 bonuses,
   5 blog posts, and core pages including a fully composed homepage).
3. Visit:
   - Site: http://localhost:8095
   - wp-admin: http://localhost:8095/wp-admin (credentials printed at the
     end of setup, from `.env`)
   - Mailhog (catches outgoing email): http://localhost:8025
   - phpMyAdmin: http://localhost:8081

Day to day, `wp-content/` is bind-mounted from this repo, so editing the
theme or plugin files takes effect immediately — no rebuild needed. Useful
commands (see `Makefile`):

```bash
make up            # start containers
make down          # stop containers
make logs          # tail logs
make bash          # shell into the wordpress container
make wp cmd="plugin list"   # run any wp-cli command
make seed          # re-run the demo content seeder (idempotent)
make backup        # dump DB + uploads into ./backups
```

## Editing content in wp-admin

- **Casinos / Bonuses / Games**: each has its own admin menu with a
  "WagerWise Details" box (rating, pros/cons, affiliate link, etc.) — this
  is a native meta box, not a third-party field plugin.
- **Global settings**: the "WagerWise" menu item — site disclaimer,
  responsible gambling text, 18+ age gate toggle, homepage hero text, social
  links, and header/footer tracking scripts (e.g. GA4).
- **Homepage & page layout**: composed entirely from custom blocks (Top
  Casinos, Bonus Grid, Comparison Table, Blog Grid, CTA Button, Hero
  Search…) — insert, reorder, or remove them like any other Gutenberg
  block. Insert the "WagerWise: Homepage Sections" pattern to reset a page
  to the default homepage layout.
- **Translations**: use the Polylang language switcher/meta box on any
  post, page, or the custom post types to add a translation.

## Production

Production runs the same `docker-compose.yml` plus `docker-compose.prod.yml`
(NOT `docker-compose.override.yml`, which is dev-only and not passed
explicitly). Code is baked into an immutable image per deploy; only the
database and `wp-content/uploads` persist across deploys.

TLS termination and HTTPS routing are handled by a shared **Traefik** reverse
proxy that already runs on this host in front of the other sites (an
external Docker network named `proxy`) — this project does not run its own
certbot or expose ports 80/443 directly; the `nginx` service just joins the
`proxy` network and carries Traefik routing labels
(`traefik.http.routers.casinoradar.*` in `docker-compose.prod.yml`).

1. On the VPS, clone this repo and create `.env` with real production
   values — a real `DOMAIN` (`casinoradar.io`), strong DB/admin credentials,
   and fresh auth salts (generate at
   https://api.wordpress.org/secret-key/1.1/salt/). Point the domain's DNS A
   record at the VPS before continuing.
2. Confirm the shared `proxy` Docker network already exists on the host
   (`docker network inspect proxy`) — it's created by the Traefik deployment,
   not by this project. Deploy:
   ```bash
   ./scripts/deploy-prod.sh
   ```
   This builds a fresh image, starts the stack (nginx joins `proxy` and
   Traefik picks it up automatically via its Docker provider), re-runs the
   (idempotent) bootstrap, and flushes caches. Traefik requests/renews the
   Let's Encrypt certificate itself — no separate cert step here.
3. **First deploy only** — seed the database and media. This repo ships a
   production-ready dump (`casinoradar-production.sql`, all URLs already
   rewritten to `https://casinoradar.io`) and its images
   (`casinoradar-uploads.tar.gz`), both at the project root.
   `entrypoint-prod.sh` deliberately never syncs `wp-content/uploads` from
   the built image — on every deploy it's excluded on purpose, so a redeploy
   can never overwrite real production uploads with whatever happens to be
   in this repo — so the images have to be loaded into the
   `wp_content_prod` volume once, manually, after the stack is up:
   ```bash
   COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"
   $COMPOSE run --rm -v "$(pwd)/casinoradar-uploads.tar.gz:/tmp/uploads.tar.gz:ro" \
     --entrypoint bash wpcli -c \
     "tar xzf /tmp/uploads.tar.gz -C /var/www/html/wp-content && chown -R www-data:www-data /var/www/html/wp-content/uploads"
   $COMPOSE run --rm -v "$(pwd)/casinoradar-production.sql:/tmp/dump.sql:ro" \
     wpcli db import /tmp/dump.sql
   ```
   Skip this step on later redeploys — it would overwrite any real content
   added after launch.
4. Back up regularly: `./scripts/backup-db.sh` (run it from a cron job on
   the VPS, or adapt it to push backups off-host).

Redis object caching is enabled automatically in production
(`docker-compose.prod.yml`).

## Notes / deliberate simplifications

- **No ACF dependency, free or paid.** Repeaters (pros/cons), the settings
  page, and "flexible content" are all hand-built on native WordPress APIs.
  Editors get the same dashboard-configurable experience without any
  licensing cost; if you later want a friendlier field-builder UI, ACF PRO
  could be layered on top without restructuring the data (all fields are
  plain post meta).
- **Theme CSS/JS is plain, hand-authored** (`assets/css/main.css`,
  `assets/js/main.js`) — no Vite/webpack build step. This keeps the Docker
  setup simple (no Node stage) at the cost of not having SCSS/bundling; for
  a site this size, plain CSS with custom properties is plenty maintainable.
- **Fonts** use a modern system-font stack rather than self-hosted
  Google Fonts, to avoid a network dependency at build/deploy time. Drop
  woff2 files into `assets/fonts/` and reference them in `theme.json` if you
  want custom brand typography later.
- **Casino logos in the demo content** are generated placeholder images
  (solid color + text), not real artwork — replace them by editing each
  Casino's featured image in wp-admin.
