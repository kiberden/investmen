[← Architecture](architecture.md) · [Back to README](../README.md) · [Deployment →](deployment.md)

# Configuration

Конфигурация проекта строится вокруг `laravel_app/.env` и `docker-compose.yml`.

## Базовый bootstrap

```bash
cp laravel_app/.env.example laravel_app/.env
```

## Ключевые переменные окружения

| Переменная | Назначение | Типичное значение |
|-----------|------------|-------------------|
| `APP_ENV` | Режим приложения | `local` |
| `APP_PORT` | Порт PHP/OCTANE сервиса | `8000` |
| `DB_USERNAME` / `DB_PASSWORD` / `DB_DATABASE` | Доступ к PostgreSQL | из `.env` |
| `DB_PORT` | Проброс порта Postgres | `5432` |
| `QUEUE_CONNECTION` | Транспорт очередей | `redis` |
| `REDIS_HOST` / `REDIS_PORT` | Подключение к Redis | `redis` / `6379` |
| `BROKER_SYSTEMS_ENABLED` | Включенные брокер-системы | `tbank` |
| `BROKER_GATEWAY_CACHE_TTL_SECONDS` | TTL кэша брокерских данных | `120` |
| `XDEBUG_MODE` | Режим Xdebug в php-контейнере | `debug,develop` |

## Конфигурационные файлы

- `laravel_app/.env.example` — шаблон локальной конфигурации.
- `laravel_app/config/*` — Laravel-конфиги приложения.
- `docker-compose.yml` — описания сервисов, сетей, healthcheck.
- `.ai-factory/config.yaml` — язык и настройки AI Factory-воркфлоу.

## Частые сценарии

```bash
# отключить Xdebug перед запуском
XDEBUG_MODE=off make up

# применить миграции
make migrate
```

## See Also

- [Architecture](architecture.md) — как связаны слои и модули.
- [Deployment](deployment.md) — порядок запуска сервисов.
- [Testing](testing.md) — проверки после изменения конфигов.
