# Архитектура Investman

## Архитектурный паттерн
Проект использует паттерн **модульного монолита** на Laravel:
- единое приложение и единый деплой;
- предметные интеграции изолированы в модулях;
- явные границы между доменной логикой, UI-слоем и инфраструктурой.

## Границы модулей

### `laravel_app/app/Modules/BrokerGateway/*`
- Основная доменная зона интеграции с брокерами.
- Содержит контракты, DTO, application-сценарии и provider-реализации.
- Все новые фичи брокерского шлюза добавляются сюда.

### `laravel_app/app/Services/BrokerGateway/*`
- Оркестрация прикладных сценариев, фабрики и сервисы доступа.
- Использует контракты модулей, не нарушая их границы.

### `laravel_app/app/MoonShine/*`
- UI/admin слой.
- Не содержит прямой интеграционной логики и API-клиентов.

### `laravel_app/app/Providers/*`
- Только DI-композиция, регистрация сервисов и биндингов.
- Без предметной бизнес-логики.

## Правила зависимостей
- UI (`MoonShine`) зависит от сервисов/контрактов, но не от инфраструктурных клиентов напрямую.
- Модуль `BrokerGateway` задает контракты и DTO, инфраструктурные адаптеры реализуют контракты.
- Jobs/Actions обращаются к сервисному слою и модулям через DI.
- Запрещено смешивать новую модульную интеграцию с legacy-потоками без адаптерного слоя.

## Рекомендованная структура слоев
```text
Modules/BrokerGateway/
├── Core/
│   ├── Contracts/
│   └── DTO/
└── Providers/<Broker>/
    ├── Application/
    ├── Infrastructure/
    └── <Broker>ModuleServiceProvider.php
```

## Пример направления зависимостей
```php
<?php

declare(strict_types=1);

namespace App\Actions\Broker;

use App\Services\BrokerGateway\Profiles\BrokerProfileService;

final class SyncBrokerAccountAction
{
    public function __construct(
        private readonly BrokerProfileService $profileService,
    ) {
    }

    public function __invoke(int $brokerId): void
    {
        $this->profileService->sync($brokerId);
    }
}
```

## Инфраструктурные решения
- Runtime: Docker Compose (`php`, `db`, `redis`, `horizon`).
- База данных: PostgreSQL.
- Очереди/кэш: Redis + Horizon.
- Наблюдаемость: Grafana/Loki/Promtail.

## Качество и проверка
- TDD-first для изменений поведения (red/green/refactor).
- Обязательная проверка форматирования и статанализа через `make mago_format_auto` и `make mago_ci_check`.
- Unit/Feature тесты в `laravel_app/tests`.
