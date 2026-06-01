# План: Конфигурация ключей подключения брокеров

Дата: 2026-06-01  
Режим: full  
Текущая ветка: `develop` (создание новой ветки отключено в `git.create_branches=false`)

## Settings

- Testing: `yes`
- Logging: `standard` (INFO для ключевых событий и WARN/ERROR для отклонений)
- Docs: `yes` (обязательный docs-checkpoint после реализации)

## Roadmap Linkage

- Milestone: `Конфигурация ключей подключения брокеров`
- Rationale: сначала фиксируем единый конфиг-формат ключей подключения для `tbank`, чтобы следующими шагами безопасно добавить select в ресурсе брокера и резолв API-настроек по выбранному ключу.

## Scope и границы

- Включено в этот план:
  - единый конфигурационный формат connection keys для брокеров;
  - реализация только рабочих ключей для `tbank`;
  - проверки структуры конфигов тестами.
- Не включено в этот план:
  - добавление/регистрация новых провайдеров (`alfa`, `sber`) и их конфигов;
  - изменение UI MoonShine (select в форме брокера);
  - изменения БД/моделей для хранения выбранного ключа в записи брокера;
  - runtime-резолв по ключу из записи брокера.

## Tasks

### Фаза 1 — Дизайн и схема конфигов

### [x] Task 1: Зафиксировать целевую схему connection keys
- Deliverable: утверждена единая схема записи connection keys для брокеров и маппинг полей для использования в последующих milestone.
- Файлы: `laravel_app/config/broker-systems/tbank.php`.
- Что сделать:
  - добавить секцию ключей подключения для `tbank` (например `connection_keys`), где каждый ключ содержит минимум `provider`, `environment`, `base_url`, `timeout`, `app_name`, `label`;
  - сохранить обратную совместимость с текущей секцией `environments`, чтобы текущий runtime не сломался.
- Logging requirements:
  - в коде runtime-логирование не добавлять (config-only шаг);
  - документировать в комментариях конфига назначение ключей и различие между `connection key` и `broker credential token`.
- Dependency notes: базовая задача для всех последующих задач.

### Фаза 2 — Агрегация и env-настройки

### [x] Task 2: Расширить агрегирующий конфиг провайдеров
- Deliverable: `broker-providers` экспортирует данные, достаточные для чтения ключей подключения в следующих milestone.
- Файлы: `laravel_app/config/broker-providers.php`.
- Что сделать:
  - добавить в результирующий массив поле для агрегированных connection keys (например, под `providers.<code>.connection_keys` или отдельную нормализованную секцию);
  - убедиться, что поведение текущих consumers (`providers`, `default_environment`, `cache_ttl_seconds`) не меняется.
- Logging requirements:
  - не добавлять runtime-логи, но оставить комментарии по ожидаемому контракту структуры.
- Dependency notes: зависит от Task 1.

### [x] Task 3: Актуализировать `.env.example` под новый конфиг-контур
- Deliverable: в `.env.example` отражены ключевые переменные для конфигов подключения.
- Файлы: `laravel_app/.env.example`.
- Что сделать:
  - добавить (или явно зафиксировать) `BROKER_GATEWAY_DEFAULT_ENV`;
  - добавить набор переменных для `tbank` connection keys (base URL/timeout/app name по средам);
  - не добавлять пользовательские API токены в env, так как токены хранятся в `broker_credentials`.
- Logging requirements:
  - логирование не требуется; в комментариях к env-переменным зафиксировать назначение.
- Dependency notes: зависит от Task 1.

### Фаза 3 — Тестовый контракт конфигов

### [x] Task 4: Обновить тест структуры `broker-systems`
- Deliverable: feature-тест валидирует новую секцию connection keys и сохраняет контракт текущих систем.
- Файлы: `laravel_app/tests/Feature/Config/BrokerSystemsConfigTest.php`.
- Что сделать:
  - проверить, что `systems.tbank.connection_keys` существует и имеет корректную структуру;
  - проверить, что включенные по умолчанию системы и текущий runtime-контракт не изменились.
- Logging requirements:
  - логирование в тестах не добавлять;
  - при падениях тестов сообщения assert должны явно указывать, какое поле схемы нарушено.
- Dependency notes: зависит от Task 1.

### [x] Task 5: Обновить тест структуры `broker-providers`
- Deliverable: feature-тест подтверждает корректную агрегацию connection keys в конечном provider-config.
- Файлы: `laravel_app/tests/Feature/Config/BrokerProvidersConfigTest.php`.
- Что сделать:
  - проверить, что для `tbank` доступны ожидаемые ключи в агрегированном конфиге;
  - проверить сохранение обратной совместимости старого контракта (`providers.tbank.environments.*`);
  - добавить негативный кейс на пустой/отсутствующий ключ (если предусмотрен контрактом).
- Logging requirements:
  - не добавлять runtime-логи;
  - использовать осмысленные assertion messages для диагностики структуры.
- Dependency notes: зависит от Task 2.

### [x] Task 6: Добавить регрессионный unit-тест фабрики провайдера
- Deliverable: unit-тест подтверждает, что добавление `connection_keys` не ломает текущий runtime-контракт `BrokerGatewayProviderFactory` и `TBankProvider`.
- Файлы: `laravel_app/tests/Unit/BrokerGateway/Factory/BrokerGatewayProviderFactoryTest.php` (new).
- Что сделать:
  - протестировать создание провайдера `tbank` при конфиге с новой секцией `connection_keys`;
  - проверить, что выбор environment и чтение `base_url`/`timeout`/`app_name` работают по прежнему контракту `environments`;
  - добавить негативный кейс на невалидную структуру provider-конфига.
- Logging requirements:
  - runtime-логирование не добавлять;
  - в assertion messages явно фиксировать, какой контракт фабрики нарушен.
- Dependency notes: зависит от Task 2.

### Фаза 4 — Верификация и документация

### [x] Task 7: Прогнать проверку качества и оформить docs-checkpoint
- Deliverable: зелёные проверки по измененным тестам и краткое обновление docs по формату connection keys.
- Файлы: `docs/configuration.md` (или профильный раздел в docs), при необходимости `docs/MVP/MVP_PLAN.md` (только синхронизация формулировок).
- Что сделать:
  - выполнить целевые тесты конфига и общий smoke по затронутому модулю;
  - добавить/обновить документацию по структуре connection keys, правилам именования и связке с последующими milestone.
- Logging requirements:
  - зафиксировать в документации expected INFO/WARN logging policy для последующего runtime-резолва (без внедрения в этом milestone).
- Dependency notes: зависит от Task 3–6.

## Commit Plan

- Commit 1 (после Task 1-2): `feat(config): add broker connection key schema and provider aggregation`
  - включает изменения в `broker-systems/tbank.php` и `broker-providers.php`.
- Commit 2 (после Task 3-6): `test(config): update broker system/provider config contract tests`
  - включает `.env.example`, тесты `Feature/Config` и unit-тест фабрики провайдера.
- Commit 3 (после Task 7): `docs(broker-gateway): document connection key configuration format`
  - включает docs-checkpoint и финальные правки.

## Критерии готовности

- Конфиги поддерживают несколько connection keys на провайдера и остаются обратно совместимыми с текущим runtime.
- Для `tbank` есть рабочие ключи подключения в согласованной схеме.
- Тесты конфигов покрывают новую структуру и проходят.
- Документация обновлена и согласована с roadmap milestone.
