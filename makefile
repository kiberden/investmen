DOCKER_IMAGE = php
ENV_FILE = ./laravel_app/.env

envs:
	cp ./laravel_app/.env.example ./laravel_app/.env

build:
	@docker compose --env-file $(ENV_FILE) build

up:
	@docker compose --env-file $(ENV_FILE) up -d --remove-orphans

down:
	@docker compose --env-file $(ENV_FILE) down

restart_app:
	@docker compose --env-file $(ENV_FILE) restart $(DOCKER_IMAGE)

make_resource:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) php artisan moonshine:resource

make_user:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) php artisan moonshine:user

migrate:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) php artisan migrate

migrate_and_seed:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) php artisan migrate --seed

shell:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) bash

optimize:
	@docker compose --env-file $(ENV_FILE) exec $(DOCKER_IMAGE) php artisan optimize