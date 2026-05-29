[← Configuration](configuration.md) · [Back to README](../README.md) · [Testing →](testing.md)

# Deployment

Документ описывает локальный deployment/запуск в Docker Compose для разработки.

## Сервисы окружения

- `php` — Laravel + Octane/FrankenPHP.
- `db` — PostgreSQL 17.
- `redis` — кэш и очереди.
- `horizon` — воркер очередей.

## Порядок запуска

```bash
cp laravel_app/.env.example laravel_app/.env
make build
make up
make migrate
```

## Полезные команды управления

```bash
make down          # остановка окружения
make restart_app   # перезапуск php сервиса
make debug_restart # reload octane для отладки
```

## Проверка состояния

```bash
docker compose --env-file ./laravel_app/.env ps
docker compose --env-file ./laravel_app/.env logs -f php
docker compose --env-file ./laravel_app/.env logs -f horizon
```

## Health checks

- `db` использует `pg_isready`.
- `redis` использует `redis-cli ping`.
- `horizon` зависит от `db` и `redis` c `service_healthy`.

## See Also

- [Configuration](configuration.md) — переменные и конфиги окружения.
- [Testing](testing.md) — проверки качества после деплоя.
- [Architecture](architecture.md) — архитектурные границы приложения.
