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

1. On the VPS, clone this repo and create `.env` with real production
   values — a real `DOMAIN`, strong DB/admin credentials, and fresh auth
   salts (generate at https://api.wordpress.org/secret-key/1.1/salt/).
   Point the domain's DNS A record at the VPS before continuing.
2. First-time only — bootstrap the TLS certificate (chicken-and-egg: nginx's
   real config requires a cert to exist before it can start):
   ```bash
   ./scripts/init-ssl.sh
   ```
3. Every deploy after that:
   ```bash
   ./scripts/deploy-prod.sh
   ```
   This builds a fresh image, restarts the stack, re-runs the (idempotent)
   bootstrap, and flushes caches. Certbot auto-renews the certificate via a
   sidecar container.
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
