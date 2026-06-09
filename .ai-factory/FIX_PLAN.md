# Fix Plan: remove read-path writes and support full multi-account sync

**Problem:** По замечаниям ревью нужно убрать запись в БД из read-path (`profilesForUser`) и исправить модель синхронизации так, чтобы для одного брокера корректно обрабатывались все счета, полученные из TBank API.
**Created:** 2026-06-09 20:29

## Analysis

Что найдено при разборе:

- Текущее присвоение `external_account_id` выполняется внутри `BrokerProfileService::profilesForBroker()`, то есть во время чтения профилей.
- Текущая модель `accounts` с `broker_id` как `unique` допускает только одну запись на брокера, тогда как по бизнес-логике для одного брокера может быть несколько брокерских счетов.
- Это приводит к потере данных по счетам и некорректной обработке мультисчетных пользователей.
- Архитектурно корректнее переносить синхронизацию идентификатора во flow синка аккаунта (action), а `BrokerProfileService` оставить read-only.

## Fix Steps

1. [x] Убрать запись в `accounts` из read-path
   - Удалить вызов `syncPrimaryExternalAccountId()` из `BrokerProfileService::profilesForBroker()`.
   - Удалить/перенести приватный метод синка из `BrokerProfileService`.
   - Убедиться, что read-path остался полностью без побочных изменений БД.

2. [x] Изменить модель хранения счетов под multi-account (schema rollout first)
   - Убрать ограничение `unique` на `accounts.broker_id`.
   - Зафиксировать безопасный порядок rollout: `drop unique(broker_id)` -> backfill `external_account_id` -> ужесточение constraints.
   - Добавить технический шаг backfill (команда/батч-сценарий), который заполняет `external_account_id` для существующих строк до включения строгих ограничений.
   - Добавить unique-ограничение по паре (`broker_id`, `external_account_id`) для идемпотентной синхронизации.
   - Явно определить стратегию для `NULL external_account_id` (предпочтительно: после backfill сделать `NOT NULL`; если нужен переходный этап — использовать временный partial unique).

3. [x] Перенести sync счетов в write-path и поддержать все полученные аккаунты
   - Добавить отдельный action синхронизации счетов (например, `SyncTBankBrokerAccountsAction`) в `Modules/.../Application`, вызываемый из `SyncBrokerAccountAction` после успешного `provider->syncAccount($broker)`.
   - Явно описать provider-aware вызов: не связывать generic flow жестко с TBank-классами (ветка по `provider_code` или capability-интерфейс).
   - Добавить явный шаг DI-регистрации новых write-path action-классов в `TBankModuleServiceProvider` (и связанных сервисах, если требуется).
   - Удалить legacy `Account::updateOrCreate(['broker_id' => ...])` из `SyncBrokerAccountAction`, чтобы не перетирать 1:N модель.
   - Реализовать получение `GetAccounts` через `TBankUsersRestClient` в write-сценарии.
   - Обновить write-path на upsert по (`broker_id`, `external_account_id`).
   - Сохранять и обновлять все счета из ответа API, а не один «основной».

4. [x] Обновить модельные связи и потребителей под multi-account
   - Обновить `Broker` relation: `account(): HasOne` -> `accounts(): HasMany`.
   - Пройтись по всем местам, где ожидается один `account`, и адаптировать к 1:N.
   - Проверить вызовы OperationsService/PortfolioService и источники `accountId`.
   - Зафиксировать правило: где нужен один счет — `accountId` передается явно; где нужен агрегат — обработка выполняется по всем полученным и сохраненным счетам.

5. [x] Обновить/добавить тесты на регрессию (конкретные файлы и кейсы)
   - Unit/Feature на сценарий с несколькими счетами (проверка полной синхронизации всех аккаунтов).
   - Feature на отсутствие мутаций `accounts` при вызове read-path `BrokerProfileService::profilesForUser()`.
   - Проверка, что sync счетов выполняется только из write-path.
   - Feature на non-TBank провайдер: новый TBank account-sync не должен выполняться для других провайдеров.
   - Добавить интеграционный тест на работу unique (`broker_id`, `external_account_id`) и повторный upsert без дублей.
   - Зафиксировать таргетные тестовые файлы: `tests/Feature/Broker/BrokerProfileServiceTest.php`, `tests/Feature/Broker/SyncBrokerAccountActionTest.php`, отдельный feature-тест на миграционные ограничения/upsert-idempotency.

6. [x] Проверить и обновить документацию/диаграммы под новую логику
   - Сверить `docs/architecture.md` (C4 и class diagram) с фактической multi-account моделью.
   - Обновить `docs/README.md` и `docs/Архитектура/overview.md`, если изменятся сущности/связи.
   - Проверить и при необходимости обновить `docs/testing.md` под новый smoke-регресс по multi-account sync.
   - Убрать временные `[FIX]` маркеры из логов/тех-комментариев в затронутых местах и привести формулировки к текущей logging policy.
   - Явно отразить изменение `Broker hasMany Account` в диаграммах и описании потоков.
   - Проверить, что описание в `.ai-factory/plans/tbank-users-operations-instruments-api.md` не конфликтует с фиксом.

## Files to Modify

- `laravel_app/app/Services/BrokerGateway/Profiles/BrokerProfileService.php` — убрать write-side effect из read-сценария.
- `laravel_app/app/Actions/Broker/SyncBrokerAccountAction.php` — подключить provider-aware sync всех внешних счетов и убрать legacy upsert по `broker_id`.
- `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Application/*` — добавить `SyncTBankBrokerAccountsAction` (или эквивалент) для write-path синхронизации счетов.
- `laravel_app/app/Modules/BrokerGateway/Providers/TBank/TBankModuleServiceProvider.php` — зарегистрировать новые write-path action-классы в DI-контейнере.
- `laravel_app/database/migrations/*` — изменить ограничение на `accounts` для поддержки multi-account.
- `laravel_app/app/Console/Commands/*` (или эквивалент batch-job) — backfill `external_account_id` для существующих строк перед `NOT NULL/UNIQUE`.
- `laravel_app/app/Models/Account.php`, `laravel_app/app/Models/Broker.php` — обновить модель/связи под multi-account.
- `laravel_app/app/Models/Broker.php`, `laravel_app/app/Observers/BrokerObserver.php` — убрать временные `[FIX]` debug-маркеры.
- `laravel_app/tests/Feature/Broker/BrokerProfileServiceTest.php` — регрессия на read-only поведение.
- `laravel_app/tests/Feature/Broker/SyncBrokerAccountActionTest.php` — сценарии синка внешнего id.
- `laravel_app/tests/Feature/Broker/*` и/или миграционные тесты — проверка unique (`broker_id`, `external_account_id`), upsert без дублей и non-TBank регрессии.
- при необходимости: `laravel_app/tests/Unit/BrokerGateway/Providers/TBank/Infrastructure/Rest/TBankUsersRestClientTest.php` (если расширяется контракт ответа/обработки).
- `docs/architecture.md`, `docs/README.md`, `docs/Архитектура/overview.md`, `docs/testing.md` — проверка и правка диаграмм/описаний и smoke-check набора.

## Risks & Considerations

- Возможна ломка текущего поведения, если код полагался на “автосинк id при чтении профилей”.
- Нужна миграция схемы без потери уже сохраненных данных в `accounts`.
- Важно сохранить обратную совместимость для текущих операций, которые ожидают один `accountId`.
- Нужно проверить, что изменения не затрагивают non-TBank провайдеры.
- Важно контролировать порядок миграций и деплой (schema change -> code switch), чтобы избежать временной несовместимости.
- Для PostgreSQL важно корректно провести этап с `NULL external_account_id`, иначе upsert/unique может работать неидемпотентно до завершения backfill.

## Test Coverage

- Тест read-path без мутаций:
  - вызов `profilesForUser()` не должен менять таблицу `accounts`.
- Тест write-path синка:
  - `SyncBrokerAccountAction` создает/обновляет все счета из `GetAccounts`.
- Тест multi-account:
  - при 2+ счетах все счета сохраняются и доступны для последующей обработки.
- Тест schema constraints:
  - уникальность (`broker_id`, `external_account_id`) работает как ожидается при повторной синхронизации.
- Тест non-TBank compatibility:
  - для non-TBank провайдера новый сценарий TBank account-sync не запускается и не меняет существующий flow.
- Тест model relations:
  - `Broker` корректно возвращает коллекцию `accounts`, а не один `account`.
- Тест docs consistency (manual check):
  - диаграммы и текст в `docs/architecture.md` и `docs/Архитектура/overview.md`, а также smoke-набор в `docs/testing.md` соответствуют итоговой реализации фикса.
