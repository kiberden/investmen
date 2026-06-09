[← Deployment](deployment.md) · [Back to README](../README.md)

# Testing

Проект использует PHPUnit и обязательные quality-checks через `mago`.

## Базовые команды

```bash
make test_unit
make mago_format_auto
make mago_ci_check
```

## Что запускать перед коммитом

1. Форматирование: `make mago_format_auto`.
2. Статические проверки: `make mago_ci_check`.
3. Таргетные тесты для измененного функционала (`php artisan test ...` в контейнере).

## Локальный pre-commit hook

Для обязательной проверки перед `git commit` используется локальный хук:

- Файл: `.git/hooks/pre-commit`.
- Переменная `REQUIRED_RULE_COMMAND` внутри файла должна содержать exact команду вашего правила.
- Пока `REQUIRED_RULE_COMMAND` пустая, commit всегда блокируется.

Проверка хука локально:

```bash
bash .git/hooks/pre-commit
```

## Контекст тестов

- Unit и Feature тесты находятся в `laravel_app/tests`.
- Конфигурация тест-раннера: `laravel_app/phpunit.xml`.
- Для интеграционных сценариев важны проверки `BrokerGateway`-модуля и T-Bank провайдера.

## T-Bank BrokerGateway checks

Рекомендуемый минимальный smoke-набор после изменений в T-Bank интеграции:

```bash
php artisan test tests/Unit/BrokerGateway/Providers/TBank/Infrastructure/Rest
php artisan test tests/Unit/BrokerGateway/Providers/TBank/Application/Instruments
php artisan test tests/Feature/Broker/FetchTBankPortfolioActionTest.php
php artisan test tests/Feature/Broker/FetchTBankPositionsActionTest.php
php artisan test tests/Feature/Broker/FetchTBankOperationsByCursorActionTest.php
php artisan test tests/Feature/Broker/FetchTBankInstrumentByIdActionTest.php
php artisan test tests/Feature/Broker/ListTBankInstrumentsActionTest.php
php artisan test tests/Feature/Broker/BrokerProfileServiceTest.php
php artisan test tests/Feature/Broker/SyncBrokerAccountActionTest.php
php artisan test tests/Feature/Broker/AccountSyncConstraintsTest.php
```

## TDD-практика

- Сначала тест (red), затем минимальная реализация (green), затем рефакторинг.
- Для изменений бизнес-поведения добавляйте или обновляйте тесты.
- Для regression-сценариев фиксируйте входные данные и ожидаемый результат в тест-кейсе.

## See Also

- [Deployment](deployment.md) — запуск окружения для тестов.
- [Configuration](configuration.md) — env-настройки, влияющие на тесты.
- [Architecture](architecture.md) — где размещать тесты по слоям.
