# План: Привязка брокера к provider_code через select в ресурсе

Дата: 2026-06-05  
Режим: full  
Текущая ветка: `develop` (создание новой ветки отключено в `git.create_branches=false`)

## Settings

- Testing: `yes`
- Logging: `minimal` (только WARN/ERROR, без DEBUG/INFO шума)
- Docs: `yes` (обязательный docs-checkpoint после реализации)

## Roadmap Linkage

- Milestone: `Привязка брокера к ключу подключения через select в ресурсе`
- Rationale: milestone закрывает UI/данные для явного выбора провайдера (`provider_code`) в записи брокера; окружение подключения определяется централизованно через `APP_ENV` в конфиге провайдера `tbank`.

## Scope и границы

- Включено:
  - хранение выбранного `provider_code` в записи брокера;
  - добавление `select` поля провайдера в форме брокера в MoonShine;
  - валидация `provider_code` по enabled providers;
  - тесты на сохранение и валидацию `provider_code`.
- Не включено:
  - runtime-резолв API-конфига по записи брокера (следующий milestone; environment берется из `APP_ENV`);
  - переработка `SyncTBankAccountAction`/`LegacyTBankProviderConfigAdapter`;
  - портфельный экран и кнопка перехода к портфелю.

## Tasks

### Фаза 1 - Модель данных для провайдера

### [x] Task 1: Добавить поле provider_code в таблицу brokers
- Deliverable: в БД появляется явное поле провайдера подключения, существующие записи получают валидное fallback-значение.
- Файлы: `laravel_app/database/migrations/*_add_provider_code_to_brokers_table.php`.
- Что сделать:
  - добавить колонку `provider_code` в таблицу `brokers`;
  - выполнить backfill из enabled providers (`broker-providers.providers`), с безопасным fallback на `tbank`, если список пуст или невалиден;
  - обеспечить безопасный rollback в down-части миграции.
- Logging requirements:
  - новые runtime-логи не добавлять;
  - ошибки миграции оставлять на стандартных exception уровня ERROR.
- Dependency notes: базовая задача, блокирует Task 2-6.

### [x] Task 2: Обновить модель Broker под поле provider_code
- Deliverable: `Broker` корректно принимает и сохраняет `provider_code` через mass-assignment.
- Файлы: `laravel_app/app/Models/Broker.php`.
- Что сделать:
  - добавить `provider_code` в `$fillable`;
  - нормализовать формат значения (lowercase/trim) на уровне модели или валидации.
- Logging requirements:
  - не добавлять новые логи;
  - оставаться в минимальной политике WARN/ERROR через текущие Laravel-механизмы.
- Dependency notes: зависит от Task 1.

### Фаза 2 - UI и валидация в админке

### [x] Task 3: Добавить select provider_code в форму брокера MoonShine
- Deliverable: в `BrokerFormPage` пользователь выбирает провайдера подключения.
- Файлы: `laravel_app/app/MoonShine/Resources/Broker/Pages/BrokerFormPage.php`.
- Что сделать:
  - добавить `Select` поле `provider_code` в форму create/edit;
  - сформировать options для `provider_code` из `config('broker-providers.providers')` в формате `code => provider_name` (с fallback на `code`, если `name` отсутствует);
  - задать дефолт для create: `provider_code=tbank`;
  - определить поведение поля на edit (editable или readonly по принятому контракту);
  - добавить комментарий/подсказку в форме, что environment выбирается автоматически из `APP_ENV`.
  - зафиксировать поведение при пустом списке providers (предсказуемая ошибка валидации/блокировка сохранения с понятным сообщением).
- Logging requirements:
  - runtime-логи не добавлять;
  - ошибки валидации/формы оставлять через стандартный Laravel/MoonShine поток (WARN на уровне UI-валидации не логируется отдельно).
- Dependency notes: зависит от Task 2.

### [x] Task 4: Добавить правила валидации provider_code в форме
- Deliverable: форма принимает только валидные значения провайдера.
- Файлы: `laravel_app/app/MoonShine/Resources/Broker/Pages/BrokerFormPage.php`.
- Что сделать:
  - расширить `rules()` правилом `required|string|in:<allowed-provider-codes>`;
  - зафиксировать create/update сценарии для поля;
  - явно обработать кейс пустого списка доступных провайдеров.
- Logging requirements:
  - дополнительных логов не добавлять;
  - невалидные входные данные обрабатываются стандартным validation error (ERROR логов вручную не писать).
- Dependency notes: зависит от Task 3.

### Фаза 3 - Тестовый контракт

### [x] Task 5: Добавить feature-тесты на сохранение и валидацию provider_code
- Deliverable: тесты подтверждают корректное сохранение валидного `provider_code` и отклонение невалидных данных.
- Файлы: `laravel_app/tests/Feature/Broker/BrokerProviderCodePersistenceTest.php` (new), `laravel_app/tests/Feature/Broker/BrokerProviderCodeValidationTest.php` (new).
- Что сделать:
  - добавить позитивные сценарии create/update с валидным `provider_code`;
  - добавить негативные сценарии: пустое значение, неизвестный провайдер, неверный тип;
  - проверить, что невалидный update не изменяет сохраненное значение.
  - в setup тестов учесть observer side-effects (`Broker::withoutEvents(...)` там, где иначе возможен побочный `user_id`/auth-контекст).
- Logging requirements:
  - логи в тестах не добавлять;
  - assertion messages должны явно указывать нарушенный контракт поля.
- Dependency notes: зависит от Task 1-4.

### [x] Task 6: Добавить регрессионный тест runtime-резолва provider_code из модели брокера
- Deliverable: регрессионный тест защищает текущий runtime-контракт, что `SyncBrokerAccountAction` использует provider из persisted `broker.provider_code`.
- Файлы: `laravel_app/tests/Feature/Broker/SyncBrokerAccountActionTest.php`.
- Что сделать:
  - добавить кейс с несколькими провайдерами в конфиге и явным `provider_code` в записи `Broker`;
  - проверить, что `SyncBrokerAccountAction` выбирает провайдер по полю модели, а не по первому элементу конфига;
  - покрыть негативный сценарий на невалидный `provider_code` (ожидаемое исключение/ошибка контракта).
- Logging requirements:
  - логи в тестах не добавлять;
  - assert-сообщения должны явно показывать, что нарушен runtime-резолв provider-кода.
- Dependency notes: зависит от Task 2.

### [x] Task 7: Расширить тест агрегации конфигурации для provider select
- Deliverable: тест подтверждает, что источник options провайдеров консистентен для UI-формы.
- Файлы: `laravel_app/tests/Feature/Config/BrokerProvidersConfigTest.php`.
- Что сделать:
  - добавить кейс, что список `providers` соответствует enabled-провайдерам;
  - добавить/обновить проверку, что default environment продолжает определяться из `APP_ENV` по текущему контракту `tbank`.
- Logging requirements:
  - логи не добавлять;
  - использовать диагностичные assert-сообщения по структуре конфига.
- Dependency notes: независимая проверка конфиг-контракта, может выполняться параллельно до финальной верификации.

### Фаза 4 - Документация и верификация

### [x] Task 8: Обновить документацию и прогнать целевые проверки
- Deliverable: документация отражает поле `provider_code` и правило определения environment через `APP_ENV`, а целевые тесты проходят.
- Файлы: `docs/configuration.md`, при необходимости `docs/architecture.md`.
- Что сделать:
  - описать источник options и границу между m1 (сохранение provider_code) и m2 (runtime-resolve по записи брокера);
  - явно зафиксировать, что environment не хранится в `brokers` и определяется через `APP_ENV`;
  - запустить таргетные тесты по новым/измененным сценариям;
  - проверить, что в плане не появилось изменений вне scope milestone.
- Logging requirements:
  - в коде новые runtime-логи не добавлять;
  - в docs зафиксировать минимальную политику логирования (WARN/ERROR only) для этого этапа.
- Dependency notes: зависит от Task 5-7.

## Commit Plan

- Commit 1 (после Task 1-2): `feat(broker): add persisted provider_code field`
- Commit 2 (после Task 3-4): `feat(moonshine): add broker provider select and validation`
- Commit 3 (после Task 5-8): `test(docs): cover broker provider runtime flow and APP_ENV rule`
