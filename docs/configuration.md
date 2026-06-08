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

## Broker Connection Keys

`laravel_app/config/broker-systems/tbank.php` содержит секцию `connection_keys` с ключами подключения `prod` и `sandbox`.

- `connection key` хранит инфраструктурные параметры (`provider`, `base_url`, `timeout`) и используется как единый источник настроек подключения.
- Пользовательский API-токен **не** хранится в config и продолжает жить в `broker_credentials`.
- Дефолтный ключ вычисляется из `APP_ENV`: `production -> prod`, любое другое значение -> `sandbox`.

## Broker Provider Selection

В записи `brokers` хранится поле `provider_code`, которое выбирается пользователем в форме ресурса брокера.

- `provider_code` определяет, какой провайдер должен быть выбран в runtime (`tbank`, `alfa`, `sber` и т.д.).
- `environment` отдельно в таблице `brokers` не хранится: для `tbank` он централизованно вычисляется из `APP_ENV` в `config/broker-systems/tbank.php`.
- UI select строится из `config('broker-providers.providers')`, поэтому список доступных провайдеров всегда синхронизирован с enabled-конфигом.

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
