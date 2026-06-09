# Implementation Plan: TBank API — Users, Operations, Instruments

Branch: develop
Created: 2026-06-09

## Settings
- Testing: yes
- Logging: minimal
- Docs: yes

## Roadmap Linkage
Milestone: "REST API-подключение как первый рабочий контур"
Rationale: Реализация расширяет рабочий REST-контур TBank до ключевых пользовательских, портфельных и инструментальных эндпоинтов.

## Commit Plan
- **Commit 1** (after tasks 1-3): `refactor: unify tbank rest request pipeline and users endpoints`
- **Commit 2** (after operations core tasks): `feat: add tbank operations clients actions and account-id mapping`
- **Commit 3** (after instruments core tasks): `feat: add instruments split rest clients and orchestration gateway`
- **Commit 4** (after quality and docs tasks): `test(docs): finalize tbank quality gate and architecture diagrams`

## Tasks

### Phase 1: REST foundation and UsersService
- [x] **Task 1: Вынести общий REST-пайплайн TBank в базовый клиент**
  Deliverable: создать переиспользуемый базовый клиент для HTTP POST вызовов TBank Invest REST gateway (общие `baseRequest`, обработка статус-кодов, исключений, валидации payload).
  Files: `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Infrastructure/Rest/TBankBaseRestClient.php`, `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Infrastructure/Rest/TBankUsersRestClient.php`, при необходимости `laravel_app/app/Services/BrokerGateway/Profiles/BrokerProfileRestClient.php`.
  Logging requirements: минимальный уровень — логировать только WARN/ERROR на границах application-слоя; в infrastructure-клиентах исключения пробрасывать без INFO/DEBUG логов.
  Dependency notes: базовый шаг для всех последующих REST-клиентов.

- [x] **Task 2: Расширить UsersService-методы в модульном клиенте**
  Deliverable: реализовать в `TBankUsersRestClient` методы `getAccounts`, `getBankAccounts`, `getInfo`, `getUserTariff` с контрактами параметров и единообразной обработкой ошибок.
  Files: `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Infrastructure/Rest/TBankUsersRestClient.php`.
  Logging requirements: WARN/ERROR только при обработке ошибок на уровне вызывающих action/service; без шумных логов успешного пути.
  Dependency notes: зависит от Task 1.

- [x] **Task 3: Перевести существующие потребители Users API на новый клиент**
  Deliverable: обновить текущие сервисы профилей/синхронизации так, чтобы вызовы `GetAccounts` и `GetInfo` шли через единый `TBankUsersRestClient`; убрать дублирующий HTTP-код в legacy-слое.
  Files: `laravel_app/app/Services/BrokerGateway/Profiles/BrokerProfileRestClient.php`, `laravel_app/app/Services/BrokerGateway/Profiles/BrokerProfileService.php`, `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Application/SyncTBankAccountAction.php`.
  Logging requirements: при fallback/ошибках интеграции писать WARN с `broker_id` и endpoint; при фатальных сбоях — ERROR с кодом ответа/типом исключения.
  Dependency notes: зависит от Task 2.

### Phase 2: OperationsService endpoints
- [x] **Task 4: Реализовать REST-клиент OperationsService**
  Deliverable: добавить методы `getPortfolio`, `getPositions`, `getOperationsByCursor` в новом `TBankOperationsRestClient`, включая сериализацию тела запроса и пагинации для cursor API.
  Files: `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Infrastructure/Rest/TBankOperationsRestClient.php`.
  Logging requirements: клиент не логирует успешные вызовы; ошибки поднимаются вверх, где фиксируются WARN/ERROR на application boundary.
  Dependency notes: зависит от Task 1.

- [x] **Task 5: Добавить application-слой для операций и портфеля**
  Deliverable: создать action/service-слой для вызовов Operations API из домена брокера (по `broker_id` с резолвом credentials и provider settings), с готовыми методами получения портфеля, позиций и операций.
  Files: `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Application/FetchTBankPortfolioAction.php`, `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Application/FetchTBankPositionsAction.php`, `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Application/FetchTBankOperationsByCursorAction.php`, обновления `laravel_app/app/Modules/BrokerGateway/Providers/TBank/TBankModuleServiceProvider.php`.
  Logging requirements: в action-слое фиксировать WARN при recoverable ошибках интеграции и ERROR при неуспешном завершении use-case; контекст: `broker_id`, `provider_code`, `operation`.
  Dependency notes: зависит от Task 4 и текущих `BrokerCredentialResolver`/`LegacyTBankProviderConfigAdapter`.

- [x] **Task 5a: Добавить хранение внешнего `account_id` для вызовов Operations API**
  Deliverable: зафиксировать источник `account_id` для `getPortfolio/getPositions/getOperationsByCursor`: добавить хранение внешнего идентификатора счета TBank в доменной модели и маппинг при синхронизации `GetAccounts`.
  Files: новая миграция в `laravel_app/database/migrations/*_add_external_account_id_to_accounts_table.php`, `laravel_app/app/Models/Account.php`, `laravel_app/app/Services/BrokerGateway/Profiles/BrokerProfileService.php`, при необходимости `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Application/SyncTBankAccountAction.php`.
  Logging requirements: WARN при невозможности сопоставить account из API с локальной записью; ERROR при невалидном состоянии данных перед вызовом Operations API.
  Dependency notes: зависит от Task 3 и Task 5.

- [x] **Task 6: Подключить Operations use-cases к текущим интеграционным точкам**
  Deliverable: интегрировать новые actions в существующие сервисы/entry points, где нужен доступ к портфелю/позициям/операциям, не нарушая границы модулей.
  Files: `laravel_app/app/Actions/Broker/FetchBrokerPortfolioAction.php`, `laravel_app/app/Actions/Broker/FetchBrokerPositionsAction.php`, `laravel_app/app/Actions/Broker/FetchBrokerOperationsByCursorAction.php`, `laravel_app/app/Services/BrokerGateway/PortfolioService.php`, `laravel_app/app/Modules/BrokerGateway/Providers/TBank/TBankModuleServiceProvider.php`.
  Logging requirements: WARN для деградации ответа внешнего API, ERROR для отказа сценария; без DEBUG/INFO шума.
  Dependency notes: зависит от Task 5 и Task 5a.

### Phase 3: InstrumentsService endpoints and quality gate
- [x] **Task 7: Реализовать узкие REST-клиенты InstrumentsService по блокам ответственности**
  Deliverable: вместо одного большого клиента добавить отдельные клиенты в namespace `Instruments` по явным зонам ответственности: `AssetRestClient`, `BondRestClient`, `EtfRestClient`, `ShareRestClient` (с покрытием соответствующих endpoint-методов).
  Files: `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Infrastructure/Rest/Instruments/AssetRestClient.php`, `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Infrastructure/Rest/Instruments/BondRestClient.php`, `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Infrastructure/Rest/Instruments/EtfRestClient.php`, `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Infrastructure/Rest/Instruments/ShareRestClient.php`.
  Logging requirements: только проброс исключений; логирование WARN/ERROR остается в вызывающем application-слое.
  Dependency notes: зависит от Task 1.

- [x] **Task 8: Добавить тонкий InstrumentsGateway и application-обертки для Instruments API**
  Deliverable: создать `InstrumentsGateway` как оркестратор вызовов узких instruments-клиентов и добавить application actions/services для использования gateway из домена брокера.
  Files: `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Application/Instruments/InstrumentsGateway.php`, `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Application/FetchTBankInstrumentByIdAction.php`, `laravel_app/app/Modules/BrokerGateway/Providers/TBank/Application/ListTBankInstrumentsAction.php`, обновления `laravel_app/app/Modules/BrokerGateway/Providers/TBank/TBankModuleServiceProvider.php`.
  Logging requirements: WARN при частичной недоступности внешнего API, ERROR при полном отказе use-case; контекст endpoint + идентификаторы сущностей.
  Dependency notes: зависит от Task 7.

- [x] **Task 8a: Подключить Instruments use-cases к реальным точкам входа**
  Deliverable: интегрировать `InstrumentsGateway`-based actions в прикладные entry points (service/actions), чтобы инструменты использовались не только как внутренний модульный код, но и как доступный сценарий домена.
  Files: `laravel_app/app/Actions/Broker/FetchBrokerInstrumentAction.php`, `laravel_app/app/Actions/Broker/ListBrokerInstrumentsAction.php`, `laravel_app/app/Services/BrokerGateway/InstrumentsService.php`, `laravel_app/app/Modules/BrokerGateway/Providers/TBank/TBankModuleServiceProvider.php`.
  Logging requirements: WARN при частичной недоступности данных по инструментам, ERROR при неуспешной оркестрации бизнес-сценария.
  Dependency notes: зависит от Task 8.

- [x] **Task 9: Закрыть quality gate тестами и документацией**
  Deliverable: добавить unit/feature тесты на новые Users/Operations/Instruments методы и обновить документацию по тестированию/REST-контру TBank.
  Files: `laravel_app/tests/Unit/BrokerGateway/Providers/TBank/Infrastructure/Rest/TBankBaseRestClientTest.php`, `laravel_app/tests/Unit/BrokerGateway/Providers/TBank/Infrastructure/Rest/TBankUsersRestClientTest.php` (расширение существующего файла), `laravel_app/tests/Unit/BrokerGateway/Providers/TBank/Infrastructure/Rest/TBankOperationsRestClientTest.php`, `laravel_app/tests/Unit/BrokerGateway/Providers/TBank/Infrastructure/Rest/Instruments/AssetRestClientTest.php`, `laravel_app/tests/Unit/BrokerGateway/Providers/TBank/Infrastructure/Rest/Instruments/BondRestClientTest.php`, `laravel_app/tests/Unit/BrokerGateway/Providers/TBank/Infrastructure/Rest/Instruments/EtfRestClientTest.php`, `laravel_app/tests/Unit/BrokerGateway/Providers/TBank/Infrastructure/Rest/Instruments/ShareRestClientTest.php`, `laravel_app/tests/Unit/BrokerGateway/Providers/TBank/Application/Instruments/InstrumentsGatewayTest.php`, `laravel_app/tests/Feature/Broker/FetchTBankPortfolioActionTest.php`, `laravel_app/tests/Feature/Broker/FetchTBankPositionsActionTest.php`, `laravel_app/tests/Feature/Broker/FetchTBankOperationsByCursorActionTest.php`, `laravel_app/tests/Feature/Broker/FetchTBankInstrumentByIdActionTest.php`, `laravel_app/tests/Feature/Broker/ListTBankInstrumentsActionTest.php`, `docs/testing.md`, `docs/architecture.md`, при необходимости `docs/broker-provider-tbank-architecture.md`.
  Logging requirements: тестовые сценарии должны проверять корректное поведение при ошибках и отсутствие избыточного лог-шума на успешном пути.
  Dependency notes: зависит от Task 3, Task 4, Task 5, Task 6, Task 7, Task 8, Task 8a.

### Phase 4: Архитектурная документация по итогам реализации
- [x] **Task 10: Подготовить и перенести в docs C4-диаграмму и диаграмму классов**
  Deliverable: по завершении реализации сформировать актуальные диаграммы добавленных компонентов и перенести их в проектную документацию: C4 (контекст/контейнер/компоненты в пределах нужного уровня для BrokerGateway/TBank) и class diagram для новых REST-клиентов, action-слоя и связанных контрактов/DTO.
  Files: канонические диаграммы хранить в `docs/architecture.md` (mermaid) и синхронно обновить навигацию в `docs/README.md` + `docs/Архитектура/overview.md`; при использовании `.puml` держать единый источник в `docs/Архитектура/` без дублирования в корне `docs/`.
  Logging requirements: в codebase логирование не добавляется; в документации явно зафиксировать точки логирования WARN/ERROR, заложенные в новых actions/services.
  Dependency notes: выполняется строго после завершения Tasks 1-9, чтобы диаграммы отражали фактическую реализацию.
  Completion note: после переноса диаграмм в docs сократить/удалить раздел `Draft Diagrams` в этом плане, чтобы избежать двух источников правды.

## Draft Diagrams (moved)

Draft diagrams were promoted to canonical docs:
- `docs/architecture.md`
- `docs/README.md`
- `docs/Архитектура/overview.md`
