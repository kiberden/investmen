DOCKER_IMAGE = php
ENV_FILE = ./laravel_app/.env

envs:
	cp ./laravel_app/.env.example ./laravel_app/.env

build:
	@docker compose --env-file $(ENV_FILE) build --no-cache

debug_restart:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) php artisan octane:reload --server=frankenphp

up:
	@docker compose --env-file $(ENV_FILE) up -d --remove-orphans

down:
	@docker compose --env-file $(ENV_FILE) down

restart_app:
	@docker compose --env-file $(ENV_FILE) restart $(DOCKER_IMAGE)

resource:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) php artisan moonshine:resource

user:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) php artisan moonshine:user

migrate:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) php artisan migrate

migrate_and_seed:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) php artisan migrate --seed

shell:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) bash

optimize:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) php artisan optimize

mago_config:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) vendor/bin/mago config

mago_files:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) vendor/bin/mago list-files

mago_format_auto:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) vendor/bin/mago format

mago_format_diff:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) vendor/bin/mago format --dry-run
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) vendor/bin/mago lint
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) vendor/bin/mago analyze

mago_ci_check:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) vendor/bin/mago format --check
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) vendor/bin/mago lint
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) vendor/bin/mago analyze

mago_baseline_lint:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) vendor/bin/mago lint --generate-baseline --baseline lint-baseline.toml

mago_baseline_analyze:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) vendor/bin/mago analyze --generate-baseline --baseline analysis-baseline.toml

test_unit:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) php artisan test --testsuite=Unit
