.PHONY: up down restart logs bash wp seed build-assets setup backup

up:
	docker compose up -d --build

down:
	docker compose down

restart:
	docker compose restart

logs:
	docker compose logs -f

bash:
	docker compose exec wordpress bash

# Usage: make wp cmd="plugin list"
wp:
	docker compose run --rm wpcli $(cmd)

seed:
	docker compose run --rm wpcli eval-file /var/www/html/wp-cli-scripts/seed.php

setup:
	./scripts/setup-local.sh

backup:
	./scripts/backup-db.sh
