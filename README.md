# Investman

> Laravel-платформа для синхронизации и мониторинга инвестиционных данных через модульный брокерский шлюз.

`investman` помогает централизованно получать брокерские данные, обновлять их в фоне и отображать в admin-интерфейсе.  
Проект ориентирован на локальную разработку в Docker и расширение под нескольких брокер-провайдеров.

## Quick Start

```bash
cp laravel_app/.env.example laravel_app/.env
make build
make up
docker compose --env-file ./laravel_app/.env exec php php artisan migrate
```

## Ключевые возможности

- **Модульный BrokerGateway** — интеграции провайдеров изолированы в `laravel_app/app/Modules/BrokerGateway`.
- **Фоновая синхронизация** — очереди и воркеры через `Redis + Horizon`.
- **Admin UI** — управление и просмотр данных через `MoonShine`.
- **Наблюдаемость** — централизованные логи через `Loki/Promtail` и дашборды в `Grafana`.

## Пример

```bash
# Проверка health статуса PostgreSQL-контейнера
docker compose --env-file ./laravel_app/.env ps db

# Запуск unit тестов в контейнере php
make test_unit
```

---

## Документация

| Гайд | Описание |
|------|----------|
| [Architecture](docs/architecture.md) | Границы модулей, зависимости, поток данных |
| [Configuration](docs/configuration.md) | Переменные окружения и ключевые конфиги |
| [Deployment](docs/deployment.md) | Сборка и запуск Docker-окружения |
| [Testing](docs/testing.md) | Тестовые сценарии и quality checks |

## Дополнительные материалы

- [MVP Plan](docs/MVP/MVP_PLAN.md)
- [Obsidian docs index](docs/README.md)
- [AI context](AGENTS.md)

## License

MIT
