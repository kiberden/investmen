# Матрица соответствия (MVP-1)

## Цель

Этот документ фиксирует текущее соответствие между требованиями MVP/архитектуры и реальной реализацией в `laravel_app`.

## Матрица

| Требование | Ожидаемое состояние | Фактическая реализация | Gap / риск | Приоритет |
|---|---|---|---|---|
| Модульная граница BrokerGateway | Интеграционная логика в `app/Modules/BrokerGateway/*`, legacy только через явные адаптеры | Модульный слой есть; добавлена явная adapter-граница `LegacyTBankProviderConfigAdapter` | Часть сервисов все еще использует legacy напрямую | P0 |
| Выбор провайдера без хардкода | Провайдер выбирается из модели/конфига/реестра | Добавлен `BrokerProviderCodeResolver`, `SyncBrokerAccountAction` больше не хардкодит `tbank` | Пока нет поля `provider_code` в таблице `brokers` | P1 |
| UI без бизнес-логики интеграции | MoonShine вызывает только Action/контракты | Сохранение credential и sync вынесены в `SyncBrokerCredentialAction` через `BrokerObserver` | Требуется e2e-проверка формы в админке | P0 |
| Рабочий минимальный UI по счетам | Раздел счетов доступен, только данные текущего пользователя | `AccountResource` переведен в `ModelResource`, добавлены поля/ограничение доступа | Нет отдельного dashboard-представления агрегатов | P1 |
| REST-first, stream как fallback в MVP-1 | stream не ломает сценарии MVP-1 | `TBankStreamClient` переведен в no-op с логом, команда возвращает понятный fallback | Нет live-обновлений (ожидаемо для MVP-1) | P2 |
| TDD / тесты на критичное поведение | Unit + Feature на реестр, sync, API-ошибки, Redis | Добавлены тесты реестра/резолвера/sync/API; Redis-тесты стабилизированы | Нет E2E-потока MoonShine | P1 |
| Quality gate | `php -l` + `make mago_ci_check` перед финализацией | Требование закреплено в документации и чеклистах | Нужна дисциплина запуска в CI/PR | P2 |

## Норматив для нового провайдера (DoR/DoD)

1. Добавить конфиг системы в `config/broker-systems/<provider>.php`.
2. Включить систему в `config/broker-systems.php` через `BROKER_SYSTEMS_ENABLED`.
3. Добавить модульную реализацию в `app/Modules/BrokerGateway/Providers/<Provider>/*`.
4. Зарегистрировать провайдер в DI и теге `broker-gateway.providers`.
5. Добавить unit/feature тесты на резолв провайдера и минимум один сценарий sync.
6. Прогнать `php -l` по измененным PHP-файлам и `make mago_ci_check`.
