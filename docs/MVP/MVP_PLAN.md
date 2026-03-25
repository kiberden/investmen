# InvestMan MVP — План разработки

## Ограничения MVP-1 (обязательно)

1. Архитектура: новая функциональность реализуется только в модульном слое `laravel_app/app/Modules/BrokerGateway/*`.
2. Практика разработки: TDD-first (`red -> green -> refactor`) для всех изменений поведения.
3. Scope MVP-1: подключение к T-Bank, получение профиля, список счетов, детализация счета (`portfolio` + `positions`), агрегаты по всем счетам, Redis TTL, ручная синхронизация.
4. Out of scope MVP-1: динамика роста на графиках, historical snapshots, полноценный stream/gRPC live-режим, multi-provider автоматизация, сложная аналитика.

---

## Матрица user stories и acceptance criteria (MVP-1)

### US-1: Подключение к банковскому профилю (T-Bank)
- Пользователь сохраняет токен в интерфейсе брокера.
- Система валидирует токен через `UsersService/GetInfo`.
- При успешной валидации создается/обновляется профиль аккаунта брокера.
- При ошибке пользователь получает понятный статус и причина логируется.

### US-2: Просмотр профиля и общей информации
- В интерфейсе отображается профиль пользователя брокера (имя, провайдер, окружение, статус синхронизации).
- Данные профиля доступны без ручного повторного ввода токена.

### US-3: Просмотр всех счетов по токену
- В интерфейсе доступен список всех брокерских счетов, возвращенных `GetAccounts`.
- Для каждого счета отображаются как минимум `id`, `type`, `status`, `name`.

### US-4: Детальное состояние конкретного счета
- Для выбранного счета отображаются:
  - текущая стоимость счета (`total_portfolio_amount`);
  - денежные средства (`total_amount_currencies`);
  - состав бумаг по позициям (инструмент, количество, текущая стоимость).
- Отображение работает из кэша и корректно обновляется через ручную синхронизацию.

### US-5: Общая информация по всем счетам
- Отображается агрегированная сводка по всем доступным счетам:
  - суммарная стоимость портфеля;
  - суммарные денежные средства;
  - количество бумаг в агрегате.

### US-6: Дашборд динамики роста по счетам
- Статус: **перенесено в MVP-2**.
- Для реализации требуется исторический слой (snapshots/time-series), не входящий в MVP-1.

---

## Границы MVP-1 и MVP-2

### MVP-1 (обязательно)
- Подключение токена и валидация профиля.
- Список счетов и детальная карточка счета.
- Агрегаты по всем счетам.
- Redis TTL cache + ручная синхронизация.

### MVP-2 (после MVP-1)
- Дашборд динамики роста по каждому счету (графики).
- Исторические срезы (snapshot storage + scheduler/job pipeline).
- Расширенная аналитика и операции.

---

## Приоритетный план MVP-1 (10 рабочих дней)

### День 1 — Архитектурный baseline
- Утвердить единый модульный контур (`Modules/BrokerGateway/*`) и DI wiring.
- Зафиксировать контракты провайдера/стрима и реестр провайдеров.

### День 2 — Конфиги и окружение
- Нормализовать `broker-systems` и `broker-providers`.
- Убрать рассинхрон конфигурации окружений и defaults.

### День 3 — TDD foundation
- Довести базовые test utilities для сервисов, HTTP fake и cache isolation.
- Закрепить минимальную test pyramid для модуля шлюза.

### День 4 — T-Bank users/accounts
- Реализовать и покрыть тестами Users REST client.
- Закрыть сценарий синхронизации аккаунта брокера.

### День 5 — Portfolio/Positions clients
- Реализовать REST-клиенты `portfolio` и `positions`.
- Добавить DTO/mapper и покрыть happy/error кейсы.

### День 6 — Redis cache layer
- Реализовать ключи с учетом provider/environment/profile/broker/account.
- Покрыть TTL и invalidate интеграционными тестами.

### День 7 — Application services
- Реализовать orchestration `cache-first -> API fallback -> refresh`.
- Закрыть unit-тесты на hit/miss/refresh/error сценарии.

### День 8 — MoonShine MVP UI
- Экраны MVP-1: профиль, список счетов, детализация счета, агрегированная сводка по всем счетам.
- Кнопка ручной синхронизации и корректный fallback по ошибкам API.

### День 9 — Автоматизация
- Команда синхронизации и scheduler без усложнения скоупа.
- Проверка без overlap и базового error handling.

### День 10 — Stabilization и документация
- Финальный рефакторинг, прогон тестов, фиксация эксплуатационных заметок.
- Проверка критериев готовности MVP-1.

---

## TDD DoD (Definition of Done)

Для каждого пользовательского сценария изменение считается завершенным только при выполнении всех пунктов:

1. Написан минимум один падающий тест до реализации (`red`).
2. Реализация доведена до `green` без отключения тестов.
3. Пройден рефакторинг без изменения поведения.
4. Присутствуют обязательные уровни проверки:
   - Unit: DTO/mapper/service логика и ветки ошибок.
   - Integration: внешние границы (HTTP client fake, Redis cache, ключи/TTL).
   - Feature: пользовательский сценарий в MoonShine/HTTP слое.
   - Lint gate: `php -l` для всех измененных PHP-файлов.
   - Standards gate: `mago` через make-цель `make mago_ci_check`.
5. Ошибки интеграции логируются и отображаются пользователю в контролируемом виде.
6. Изменение не нарушает модульную архитектуру (`Modules/BrokerGateway/*`).

---

## 📋 Описание MVP

**Цель:** Реализовать базовый функционал получения и отображения данных инвестиционного портфеля из T-Bank API с использованием Redis для кэширования и MoonShine для отображения в админ-панели.

### Ключевые принципы

- **TDD (Test-Driven Development)** — каждый этап начинается с написания тестов
- **Redis-first** — все данные из API кэшируются в Redis на 120 секунд
- **REST API T-Bank** — получение данных через REST endpoints
- **MoonShine UI** — отображение данных в админ-панели
- **Автообновление (polling)** — обновление данных по расписанию, без stream в MVP-1

---

## 🏗 Архитектура MVP

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│   MoonShine UI  │────▶│  Laravel Backend │────▶│   Redis Cache   │
│  (админ-панель) │     │   (сервисы)      │     │  (TTL 120 сек)  │
└─────────────────┘     └──────────────────┘     └─────────────────┘
                               │
                               ▼
                        ┌─────────────────┐
                        │   T-Bank API    │
                        │   (REST gRPC)   │
                        └─────────────────┘
```

### Поток данных

1. Пользователь открывает страницу в MoonShine
2. Backend проверяет кэш в Redis
3. Если кэш есть и не истёк — возвращает из кэша
4. Если кэша нет или истёк — запрос к T-Bank API → сохранение в Redis → возврат данных
5. MoonShine отображает данные

---

## 📅 Этап 1: Подготовка инфраструктуры

**Длительность:** 2-3 дня

### 1.1. Настройка Redis

**Задачи:**
- [ ] Проверить подключение Redis в docker-compose
- [ ] Настроить конфигурацию Redis в `.env`
- [ ] Протестировать подключение

**Файлы для проверки:**
```
docker-compose.yml (redis сервис)
laravel_app/.env (REDIS_HOST, REDIS_PORT, REDIS_PASSWORD)
laravel_app/config/database.php (redis configuration)
```

**Тесты:**
```php
// tests/Feature/Redis/RedisConnectionTest.php
test('redis connection is available')
test('redis can store and retrieve values')
test('redis ttl is working correctly')
```

---

### 1.2. Настройка тестового окружения

**Задачи:**
- [ ] Настроить PHPUnit для тестирования
- [ ] Создать базовый TestCase для сервисов
- [ ] Настроить моки для HTTP клиентов
- [ ] Настроить моки для Redis

**Файлы для создания:**
```
tests/TestCase.php (обновить)
tests/ServiceTestCase.php (базовый класс для сервисных тестов)
phpunit.xml (проверить конфигурацию)
```

**Тесты:**
```php
// tests/Unit/ExampleServiceTest.php
test('example service can be instantiated')
test('example service returns expected value')
```

---

### 1.3. Конфигурация T-Bank API

**Задачи:**
- [ ] Проверить конфигурацию в `broker-providers.php`
- [ ] Добавить переменные окружения для T-Bank
- [ ] Создать файл с примерами `.env.example`

**Файлы для обновления:**
```
laravel_app/.env.example
laravel_app/config/broker-providers.php
```

**Добавить в `.env.example`:**
```env
# T-Bank API Configuration
TBANK_PROD_BASE_URL=https://invest-public-api.tbank.ru/rest
TBANK_SANDBOX_BASE_URL=https://sandbox-invest-public-api.tbank.ru/rest
TBANK_PROD_TIMEOUT=10
TBANK_SANDBOX_TIMEOUT=10
TBANK_APP_NAME=investman
TBANK_DEFAULT_ENV=sandbox

# Redis Cache Configuration
CACHE_TTL_SECONDS=120
```

**Тесты:**
```php
// tests/Feature/Config/BrokerProvidersConfigTest.php
test('broker providers config is valid')
test('tbank provider has required configuration')
test('tbank environments are configured')
```

---

## 📅 Этап 2: Кэширование в Redis

**Длительность:** 3-4 дня

### 2.1. Интерфейс репозитория кэша

**Задачи:**
- [ ] Создать контракт `CacheRepositoryInterface`
- [ ] Определить методы для работы с кэшем

**Файлы для создания:**
```php
// app/Services/BrokerGateway/Cache/Contracts/CacheRepositoryInterface.php
<?php

declare(strict_types=1);

namespace App\Services\BrokerGateway\Cache\Contracts;

interface CacheRepositoryInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl): void;
    public function has(string $key): bool;
    public function forget(string $key): void;
    public function generateKey(string ...$parts): string;
}
```

**Тесты:**
```php
// tests/Unit/Cache/Contracts/CacheRepositoryInterfaceTest.php
test('interface defines required methods')
test('interface methods have correct signatures')
```

---

### 2.2. Реализация репозитория кэша

**Задачи:**
- [ ] Реализовать `PortfolioCacheRepository`
- [ ] Реализовать генерацию ключей
- [ ] Реализовать TTL 120 секунд
- [ ] Добавить логирование операций

**Файлы для создания:**
```php
// app/Services/BrokerGateway/Cache/PortfolioCacheRepository.php
<?php

declare(strict_types=1);

namespace App\Services\BrokerGateway\Cache;

use App\Services\BrokerGateway\Cache\Contracts\CacheRepositoryInterface;
use Illuminate\Support\Facades\Cache;

final class PortfolioCacheRepository implements CacheRepositoryInterface
{
    private const DEFAULT_TTL = 120;
    private const KEY_PREFIX = 'broker';

    public function get(string $key): mixed
    {
        return Cache::get($key);
    }

    public function set(string $key, mixed $value, int $ttl = self::DEFAULT_TTL): void
    {
        Cache::set($key, $value, $ttl);
    }

    public function has(string $key): bool
    {
        return Cache::has($key);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function generateKey(string ...$parts): string
    {
        return implode(':', [self::KEY_PREFIX, ...$parts]);
    }

    // Специфичные методы для портфеля
    public function getPortfolio(string $provider, string $env, int $brokerId, string $accountId): ?array
    {
        $key = $this->generatePortfolioKey($provider, $env, $brokerId, $accountId);
        return $this->get($key);
    }

    public function setPortfolio(string $provider, string $env, int $brokerId, string $accountId, array $data): void
    {
        $key = $this->generatePortfolioKey($provider, $env, $brokerId, $accountId);
        $this->set($key, $data);
    }

    private function generatePortfolioKey(string $provider, string $env, int $brokerId, string $accountId): string
    {
        return $this->generateKey($provider, $env, 'broker', (string)$brokerId, 'account', $accountId, 'portfolio');
    }
}
```

**Тесты:**
```php
// tests/Unit/Cache/PortfolioCacheRepositoryTest.php
test('can store and retrieve portfolio data')
test('portfolio data expires after ttl')
test('cache key is generated correctly')
test('portfolio key format is correct')
test('forget removes data from cache')
test('has returns true for existing key')
test('has returns false for missing key')
```

---

### 2.3. Тестирование кэширования

**Задачи:**
- [ ] Написать интеграционные тесты с Redis
- [ ] Протестировать TTL
- [ ] Протестировать генерацию ключей

**Файлы для создания:**
```php
// tests/Integration/Cache/RedisCacheIntegrationTest.php
test('redis stores portfolio data correctly')
test('redis ttl is set to 120 seconds')
test('multiple cache keys do not conflict')
test('cache can store complex data structures')
```

---

## 📅 Этап 3: T-Bank API Client

**Длительность:** 4-5 дней

### 3.1. Базовый HTTP клиент

**Задачи:**
- [ ] Обновить `TBankUsersRestClient` с тестами
- [ ] Создать базовый класс для REST клиентов
- [ ] Добавить обработку ошибок
- [ ] Добавить логирование запросов

**Файлы для создания:**
```php
// app/Modules/BrokerGateway/Providers/TBank/Infrastructure/Rest/TBankBaseRestClient.php
<?php

declare(strict_types=1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

abstract class TBankBaseRestClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
        private readonly int $timeout,
        private readonly string $appName,
    ) {}

    protected function baseRequest(): PendingRequest
    {
        return Http::asJson()
            ->acceptJson()
            ->timeout($this->timeout)
            ->withToken($this->token)
            ->withHeaders([
                'x-app-name' => $this->appName,
                'x-request-id' => uniqid('tbank_', true),
            ]);
    }

    protected function buildUrl(string $method): string
    {
        return rtrim($this->baseUrl, '/') . '/' . ltrim($method, '/');
    }

    protected function handleResponse(Response $response, string $method): array
    {
        if (!$response->successful()) {
            throw new TBankApiException(
                sprintf('T-Bank API %s failed with status %d: %s', $method, $response->status(), $response->body()),
                $response->status()
            );
        }

        $payload = $response->json();

        if (!is_array($payload)) {
            throw new TBankApiException('T-Bank API returned invalid JSON response');
        }

        return $payload;
    }
}

// app/Modules/BrokerGateway/Providers/TBank/Infrastructure/Rest/Exception/TBankApiException.php
<?php

declare(strict_types=1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Exception;

use RuntimeException;

final class TBankApiException extends RuntimeException
{
    public function __construct(string $message, int $httpCode = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $httpCode, $previous);
    }
}
```

**Тесты:**
```php
// tests/Unit/BrokerGateway/TBank/Infrastructure/Rest/TBankBaseRestClientTest.php
test('base request includes auth token')
test('base request includes app name header')
test('base request has correct timeout')
test('build url creates correct endpoint')
test('handle response throws exception on error')
test('handle response returns array on success')
test('handle response throws exception on invalid json')
```

---

### 3.2. Клиент портфеля (Portfolio)

**Задачи:**
- [ ] Создать `TBankPortfolioRestClient`
- [ ] Реализовать метод `getPortfolio()`
- [ ] Создать DTO для портфеля
- [ ] Написать тесты с моками

**Файлы для создания:**
```php
// app/Modules/BrokerGateway/Providers/TBank/Infrastructure/Rest/TBankPortfolioRestClient.php
<?php

declare(strict_types=1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Exception\TBankApiException;
use Illuminate\Http\Client\ConnectionException;
use RuntimeException;
use Throwable;

final class TBankPortfolioRestClient extends TBankBaseRestClient
{
    /**
     * Получает данные портфеля из метода PortfolioStream/GetPortfolio
     *
     * @param string $accountId ID счёта
     * @return array<string, mixed> Данные портфеля
     * @throws TBankApiException
     */
    public function getPortfolio(string $accountId): array
    {
        try {
            $response = $this->baseRequest()
                ->post($this->buildUrl('tinkoff.public.invest.api.contract.v1.PortfolioStream/GetPortfolio'), [
                    'account_id' => $accountId,
                    'currency' => 'RUB',
                ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Failed to connect to T-Bank Portfolio API.', previous: $e);
        } catch (Throwable $e) {
            throw new RuntimeException('Failed to fetch portfolio data.', previous: $e);
        }

        return $this->handleResponse($response, 'GetPortfolio');
    }
}

// app/Modules/BrokerGateway/Providers/TBank/DTO/PortfolioDto.php
<?php

declare(strict_types=1);

namespace App\Modules\BrokerGateway\Providers\TBank\DTO;

final readonly class PortfolioDto
{
    /**
     * @param array<array{figi: string, instrument_type: string, quantity: string, average_position_price: array{value: string, currency: string}, current_price: array{value: string, currency: string}}> $positions
     */
    public function __construct(
        public string $accountId,
        public string $currency,
        public string $totalAmountBonds,
        public string $totalAmountCurrencies,
        public string $totalAmountEtf,
        public string $totalAmountFutures,
        public string $totalAmountShares,
        public string $totalPortfolioAmount,
        public array $positions,
        public \DateTimeInterface $lastUpdated,
    ) {}

    public static function fromApiResponse(array $data, string $accountId): self
    {
        $positions = data_get($data, 'positions', []);
        $totalAmount = data_get($data, 'totalPortfolioAmount', '0');
        
        return new self(
            accountId: $accountId,
            currency: 'RUB',
            totalAmountBonds: data_get($data, 'totalAmountBonds', '0'),
            totalAmountCurrencies: data_get($data, 'totalAmountCurrencies', '0'),
            totalAmountEtf: data_get($data, 'totalAmountEtf', '0'),
            totalAmountFutures: data_get($data, 'totalAmountFutures', '0'),
            totalAmountShares: data_get($data, 'totalAmountShares', '0'),
            totalPortfolioAmount: $totalAmount,
            positions: is_array($positions) ? $positions : [],
            lastUpdated: now(),
        );
    }

    public function toArray(): array
    {
        return [
            'account_id' => $this->accountId,
            'currency' => $this->currency,
            'total_amount_bonds' => $this->totalAmountBonds,
            'total_amount_currencies' => $this->totalAmountCurrencies,
            'total_amount_etf' => $this->totalAmountEtf,
            'total_amount_futures' => $this->totalAmountFutures,
            'total_amount_shares' => $this->totalAmountShares,
            'total_portfolio_amount' => $this->totalPortfolioAmount,
            'positions' => $this->positions,
            'last_updated' => $this->lastUpdated->format('Y-m-d H:i:s'),
        ];
    }
}
```

**Тесты:**
```php
// tests/Unit/BrokerGateway/TBank/Infrastructure/Rest/TBankPortfolioRestClientTest.php
test('get portfolio sends correct request')
test('get portfolio returns valid data structure')
test('get portfolio throws exception on connection error')
test('get portfolio throws exception on api error')
test('get portfolio includes account id in request')
test('get portfolio sets currency to rub')

// tests/Unit/BrokerGateway/TBank/DTO/PortfolioDtoTest.php
test('portfolio dto can be created from api response')
test('portfolio dto handles missing fields')
test('portfolio dto to array returns correct structure')
test('portfolio dto includes last updated timestamp')
```

---

### 3.3. Клиент позиций (Positions)

**Задачи:**
- [ ] Создать `TBankPositionsRestClient`
- [ ] Реализовать метод `getPositions()`
- [ ] Создать DTO для позиций
- [ ] Написать тесты с моками

**Файлы для создания:**
```php
// app/Modules/BrokerGateway/Providers/TBank/Infrastructure/Rest/TBankPositionsRestClient.php
<?php

declare(strict_types=1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest;

final class TBankPositionsRestClient extends TBankBaseRestClient
{
    /**
     * Получает данные позиций из метода PortfolioStream/GetPositions
     *
     * @param string $accountId ID счёта
     * @return array<string, mixed> Данные позиций
     */
    public function getPositions(string $accountId): array
    {
        $response = $this->baseRequest()
            ->post($this->buildUrl('tinkoff.public.invest.api.contract.v1.PortfolioStream/GetPositions'), [
                'account_id' => $accountId,
            ]);

        return $this->handleResponse($response, 'GetPositions');
    }
}

// app/Modules/BrokerGateway/Providers/TBank/DTO/PositionsDto.php
<?php

declare(strict_types=1);

namespace App\Modules\BrokerGateway\Providers\TBank\DTO;

final readonly class PositionsDto
{
    /**
     * @param array<array{figi: string, instrument_type: string, quantity: string, blocked: string}> $positions
     */
    public function __construct(
        public string $accountId,
        public array $positions,
        public \DateTimeInterface $lastUpdated,
    ) {}

    public static function fromApiResponse(array $data, string $accountId): self
    {
        $positions = data_get($data, 'positions', []);
        
        return new self(
            accountId: $accountId,
            positions: is_array($positions) ? $positions : [],
            lastUpdated: now(),
        );
    }

    public function toArray(): array
    {
        return [
            'account_id' => $this->accountId,
            'positions' => $this->positions,
            'last_updated' => $this->lastUpdated->format('Y-m-d H:i:s'),
        ];
    }
}
```

**Тесты:**
```php
// tests/Unit/BrokerGateway/TBank/Infrastructure/Rest/TBankPositionsRestClientTest.php
test('get positions sends correct request')
test('get positions returns valid data structure')
test('get positions handles empty positions')

// tests/Unit/BrokerGateway/TBank/DTO/PositionsDtoTest.php
test('positions dto can be created from api response')
test('positions dto to array returns correct structure')
```

---

### 3.4. Клиент счетов (Accounts)

**Задачи:**
- [ ] Обновить `TBankUsersRestClient` для получения счетов
- [ ] Реализовать метод `getAccounts()`
- [ ] Создать DTO для счетов
- [ ] Написать тесты с моками

**Файлы для создания:**
```php
// app/Modules/BrokerGateway/Providers/TBank/Infrastructure/Rest/TBankUsersRestClient.php (обновить)
<?php

declare(strict_types=1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest;

final class TBankUsersRestClient extends TBankBaseRestClient
{
    /**
     * @return array<string, mixed>
     */
    public function getInfo(): array
    {
        $response = $this->baseRequest()
            ->post($this->buildUrl('tinkoff.public.invest.api.contract.v1.UsersService/GetInfo'), []);

        return $this->handleResponse($response, 'GetInfo');
    }

    /**
     * Получает список счетов пользователя
     *
     * @return array<string, mixed>
     */
    public function getAccounts(): array
    {
        $response = $this->baseRequest()
            ->post($this->buildUrl('tinkoff.public.invest.api.contract.v1.UsersService/GetAccounts'), []);

        return $this->handleResponse($response, 'GetAccounts');
    }
}

// app/Modules/BrokerGateway/Providers/TBank/DTO/AccountsDto.php
<?php

declare(strict_types=1);

namespace App\Modules\BrokerGateway\Providers\TBank\DTO;

final readonly class AccountsDto
{
    /**
     * @param array<array{id: string, name: string, status: string, type: string}> $accounts
     */
    public function __construct(
        public array $accounts,
        public \DateTimeInterface $lastUpdated,
    ) {}

    public static function fromApiResponse(array $data): self
    {
        $accounts = data_get($data, 'accounts', []);
        
        return new self(
            accounts: is_array($accounts) ? $accounts : [],
            lastUpdated: now(),
        );
    }

    public function toArray(): array
    {
        return [
            'accounts' => $this->accounts,
            'last_updated' => $this->lastUpdated->format('Y-m-d H:i:s'),
        ];
    }
}
```

**Тесты:**
```php
// tests/Unit/BrokerGateway/TBank/Infrastructure/Rest/TBankUsersRestClientTest.php
test('get info returns user data')
test('get accounts returns list of accounts')
test('get accounts handles empty list')
```

---

## 📅 Этап 4: Сервисный слой

**Длительность:** 4-5 дней

### 4.1. Сервис портфеля

**Задачи:**
- [ ] Создать `PortfolioService`
- [ ] Интегрировать кэширование
- [ ] Интегрировать REST клиент
- [ ] Добавить логику обновления

**Файлы для создания:**
```php
// app/Services/BrokerGateway/Providers/TBank/Services/PortfolioService.php
<?php

declare(strict_types=1);

namespace App\Services\BrokerGateway\Providers\TBank\Services;

use App\Models\Broker;
use App\Models\BrokerCredential;
use App\Modules\BrokerGateway\Providers\TBank\DTO\PortfolioDto;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankPortfolioRestClient;
use App\Services\BrokerGateway\Cache\PortfolioCacheRepository;
use App\Services\BrokerGateway\Credentials\BrokerCredentialResolver;

final class PortfolioService
{
    public function __construct(
        private readonly TBankPortfolioRestClient $restClient,
        private readonly PortfolioCacheRepository $cache,
        private readonly BrokerCredentialResolver $credentialResolver,
    ) {}

    public function getPortfolio(Broker $broker, string $accountId): PortfolioDto
    {
        // Проверяем кэш
        $cachedData = $this->cache->getPortfolio('tbank', 'sandbox', (int)$broker->getKey(), $accountId);
        
        if ($cachedData !== null) {
            return PortfolioDto::fromApiResponse($cachedData, $accountId);
        }

        // Кэша нет - запрашиваем API
        $portfolioData = $this->fetchFromApi($broker, $accountId);
        
        // Сохраняем в кэш
        $this->cache->setPortfolio('tbank', 'sandbox', (int)$broker->getKey(), $accountId, $portfolioData);

        return PortfolioDto::fromApiResponse($portfolioData, $accountId);
    }

    public function refreshPortfolio(Broker $broker, string $accountId): PortfolioDto
    {
        // Принудительное обновление
        $portfolioData = $this->fetchFromApi($broker, $accountId);
        $this->cache->setPortfolio('tbank', 'sandbox', (int)$broker->getKey(), $accountId, $portfolioData);
        
        return PortfolioDto::fromApiResponse($portfolioData, $accountId);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchFromApi(Broker $broker, string $accountId): array
    {
        $credential = $this->credentialResolver->resolveForEnvironment($broker, 'sandbox');
        $token = $this->credentialResolver->decryptToken($credential);

        $client = new TBankPortfolioRestClient(
            baseUrl: config('broker-providers.providers.tbank.environments.sandbox.base_url'),
            token: $token,
            timeout: config('broker-providers.providers.tbank.environments.sandbox.timeout'),
            appName: config('broker-providers.providers.tbank.environments.sandbox.app_name'),
        );

        return $client->getPortfolio($accountId);
    }
}
```

**Тесты:**
```php
// tests/Unit/BrokerGateway/TBank/Services/PortfolioServiceTest.php
test('get portfolio returns cached data when available')
test('get portfolio fetches from api when cache is empty')
test('get portfolio stores result in cache')
test('refresh portfolio always fetches from api')
test('refresh portfolio updates cache')
test('get portfolio uses correct cache key')
```

---

### 4.2. Сервис позиций

**Задачи:**
- [ ] Создать `PositionsService`
- [ ] Интегрировать кэширование
- [ ] Интегрировать REST клиент

**Файлы для создания:**
```php
// app/Services/BrokerGateway/Providers/TBank/Services/PositionsService.php
<?php

declare(strict_types=1);

namespace App\Services\BrokerGateway\Providers\TBank\Services;

use App\Models\Broker;
use App\Modules\BrokerGateway\Providers\TBank\DTO\PositionsDto;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankPositionsRestClient;
use App\Services\BrokerGateway\Cache\PortfolioCacheRepository;
use App\Services\BrokerGateway\Credentials\BrokerCredentialResolver;

final class PositionsService
{
    public function __construct(
        private readonly TBankPositionsRestClient $restClient,
        private readonly PortfolioCacheRepository $cache,
        private readonly BrokerCredentialResolver $credentialResolver,
    ) {}

    public function getPositions(Broker $broker, string $accountId): PositionsDto
    {
        $cachedData = $this->cache->getPositions('tbank', 'sandbox', (int)$broker->getKey(), $accountId);
        
        if ($cachedData !== null) {
            return PositionsDto::fromApiResponse($cachedData, $accountId);
        }

        $positionsData = $this->fetchFromApi($broker, $accountId);
        $this->cache->setPositions('tbank', 'sandbox', (int)$broker->getKey(), $accountId, $positionsData);

        return PositionsDto::fromApiResponse($positionsData, $accountId);
    }

    public function refreshPositions(Broker $broker, string $accountId): PositionsDto
    {
        $positionsData = $this->fetchFromApi($broker, $accountId);
        $this->cache->setPositions('tbank', 'sandbox', (int)$broker->getKey(), $accountId, $positionsData);
        
        return PositionsDto::fromApiResponse($positionsData, $accountId);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchFromApi(Broker $broker, string $accountId): array
    {
        $credential = $this->credentialResolver->resolveForEnvironment($broker, 'sandbox');
        $token = $this->credentialResolver->decryptToken($credential);

        $client = new TBankPositionsRestClient(
            baseUrl: config('broker-providers.providers.tbank.environments.sandbox.base_url'),
            token: $token,
            timeout: config('broker-providers.providers.tbank.environments.sandbox.timeout'),
            appName: config('broker-providers.providers.tbank.environments.sandbox.app_name'),
        );

        return $client->getPositions($accountId);
    }
}
```

**Тесты:**
```php
// tests/Unit/BrokerGateway/TBank/Services/PositionsServiceTest.php
test('get positions returns cached data when available')
test('get positions fetches from api when cache is empty')
test('get positions stores result in cache')
test('refresh positions always fetches from api')
```

---

### 4.3. Сервис счетов

**Задачи:**
- [ ] Создать `AccountsService`
- [ ] Интегрировать кэширование
- [ ] Интегрировать REST клиент

**Файлы для создания:**
```php
// app/Services/BrokerGateway/Providers/TBank/Services/AccountsService.php
<?php

declare(strict_types=1);

namespace App\Services\BrokerGateway\Providers\TBank\Services;

use App\Models\Broker;
use App\Modules\BrokerGateway\Providers\TBank\DTO\AccountsDto;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankUsersRestClient;
use App\Services\BrokerGateway\Cache\PortfolioCacheRepository;
use App\Services\BrokerGateway\Credentials\BrokerCredentialResolver;

final class AccountsService
{
    public function __construct(
        private readonly TBankUsersRestClient $restClient,
        private readonly PortfolioCacheRepository $cache,
        private readonly BrokerCredentialResolver $credentialResolver,
    ) {}

    public function getAccounts(Broker $broker): AccountsDto
    {
        $cachedData = $this->cache->getAccounts('tbank', 'sandbox', (int)$broker->getKey());
        
        if ($cachedData !== null) {
            return AccountsDto::fromApiResponse($cachedData);
        }

        $accountsData = $this->fetchFromApi($broker);
        $this->cache->setAccounts('tbank', 'sandbox', (int)$broker->getKey(), $accountsData);

        return AccountsDto::fromApiResponse($accountsData);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchFromApi(Broker $broker): array
    {
        $credential = $this->credentialResolver->resolveForEnvironment($broker, 'sandbox');
        $token = $this->credentialResolver->decryptToken($credential);

        $client = new TBankUsersRestClient(
            baseUrl: config('broker-providers.providers.tbank.environments.sandbox.base_url'),
            token: $token,
            timeout: config('broker-providers.providers.tbank.environments.sandbox.timeout'),
            appName: config('broker-providers.providers.tbank.environments.sandbox.app_name'),
        );

        return $client->getAccounts();
    }
}
```

**Тесты:**
```php
// tests/Unit/BrokerGateway/TBank/Services/AccountsServiceTest.php
test('get accounts returns cached data when available')
test('get accounts fetches from api when cache is empty')
test('get accounts stores result in cache')
```

---

## 📅 Этап 5: MoonShine UI

**Длительность:** 4-5 дней

### 5.1. Dashboard с портфелем

**Задачи:**
- [ ] Обновить `Dashboard.php`
- [ ] Добавить виджет с общей суммой портфеля
- [ ] Добавить виджет с позициями
- [ ] Добавить кнопку обновления

**Файлы для обновления:**
```php
// app/MoonShine/Pages/Dashboard.php
<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Broker;
use App\Services\BrokerGateway\Providers\TBank\Services\PortfolioService;
use Illuminate\Support\Facades\Auth;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Support\ListOf;

class Dashboard extends Page
{
    /**
     * @var list<ComponentContract|string>
     */
    protected array $components = [];

    public function getTitle(): string
    {
        return 'Инвестиционный портфель';
    }

    /**
     * @return ListOf<ComponentContract>
     */
    protected function components(): ListOf
    {
        return $this->components;
    }

    public function components(): array
    {
        $user = Auth::user();
        $broker = Broker::where('user_id', $user->getKey())->first();
        
        $portfolioData = null;
        $positionsData = null;
        
        if ($broker !== null && $broker->account !== null) {
            $portfolioService = app(PortfolioService::class);
            $portfolioData = $portfolioService->getPortfolio($broker, $broker->account->id);
        }

        return [
            \MoonShine\UI\Components\Layout\Box::make([
                \MoonShine\UI\Components\Layout\Column::make([
                    \MoonShine\UI\Components\Layout\Grid::make([
                        \MoonShine\UI\Components\Layout\Column::make([
                            \MoonShine\UI\Components\Metrics\Separated::make([
                                'Портфель' => $portfolioData?->totalPortfolioAmount ?? '0',
                                'Акции' => $portfolioData?->totalAmountShares ?? '0',
                                'Облигации' => $portfolioData?->totalAmountBonds ?? '0',
                                'ETF' => $portfolioData?->totalAmountEtf ?? '0',
                            ])->setVertical(true),
                        ])->onlyOnIndex(),
                    ])->columns(1),
                ])->onlyOnIndex(),
            ]),
        ];
    }
}
```

**Тесты:**
```php
// tests/Feature/MoonShine/DashboardTest.php
test('dashboard page is accessible')
test('dashboard shows portfolio data')
test('dashboard shows zero when no broker connected')
test('dashboard refresh button is visible')
```

---

### 5.2. Страница брокера с данными

**Задачи:**
- [ ] Обновить `BrokerFormPage.php`
- [ ] Добавить секцию с данными портфеля
- [ ] Добавить кнопку синхронизации
- [ ] Добавить отображение позиций

**Файлы для обновления:**
```php
// app/MoonShine/Resources/Broker/Pages/BrokerFormPage.php
<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Broker\Pages;

use App\Models\Broker;
use App\Services\BrokerGateway\Providers\TBank\Services\PortfolioService;
use App\Services\BrokerGateway\Providers\TBank\Services\PositionsService;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Form;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Metrics\Separated;

/**
 * @extends Page<Broker>
 */
class BrokerFormPage extends Page
{
    /**
     * @var list<ComponentContract|string>
     */
    protected array $components = [];

    public function getTitle(): string
    {
        return $this->getItem()?->profile_name ?? 'Брокер';
    }

    /**
     * @return ListOf<ComponentContract>
     */
    protected function components(): ListOf
    {
        return $this->components;
    }

    public function components(): array
    {
        $broker = $this->getItem();
        
        $portfolioBlock = null;
        $positionsBlock = null;
        
        if ($broker !== null && $broker->account !== null) {
            $portfolioService = app(PortfolioService::class);
            $positionsService = app(PositionsService::class);
            
            $portfolio = $portfolioService->getPortfolio($broker, $broker->account->id);
            $positions = $positionsService->getPositions($broker, $broker->account->id);
            
            $portfolioBlock = Box::make([
                Grid::make([
                    Column::make([
                        Separated::make([
                            'Общая сумма' => $portfolio->totalPortfolioAmount,
                            'Акции' => $portfolio->totalAmountShares,
                            'Облигации' => $portfolio->totalAmountBonds,
                            'ETF' => $portfolio->totalAmountEtf,
                            'Валюта' => $portfolio->totalAmountCurrencies,
                        ])->setVertical(true),
                    ]),
                ])->columns(1),
            ])->name('Портфель');
            
            $positionsCount = count($positions->positions);
            $positionsBlock = Box::make([
                Separated::make([
                    'Количество позиций' => (string)$positionsCount,
                    'Последнее обновление' => $positions->lastUpdated->format('d.m.Y H:i:s'),
                ])->setVertical(true),
            ])->name('Позиции');
        }

        return [
            Form::top()->components([
                $portfolioBlock,
                $positionsBlock,
            ]),
            
            Form::main()->components([
                // поля формы брокера
            ]),
        ];
    }
}
```

**Тесты:**
```php
// tests/Feature/MoonShine/BrokerFormPageTest.php
test('broker form page is accessible')
test('broker form shows portfolio data when available')
test('broker form shows positions count')
test('broker form shows last update time')
test('broker form handles missing account gracefully')
```

---

### 5.3. Action для синхронизации

**Задачи:**
- [ ] Создать MoonShine Action для ручной синхронизации
- [ ] Добавить кнопку на страницу брокера
- [ ] Реализовать перенаправление на обновление

**Файлы для создания:**
```php
// app/MoonShine/Actions/Broker/SyncBrokerDataAction.php
<?php

declare(strict_types=1);

namespace App\MoonShine\Actions\Broker;

use App\Models\Broker;
use App\Services\BrokerGateway\Providers\TBank\Services\PortfolioService;
use App\Services\BrokerGateway\Providers\TBank\Services\PositionsService;
use MoonShine\Actions\Action;
use MoonShine\Results\ActionResult;

class SyncBrokerDataAction extends Action
{
    protected string $label = 'Синхронизировать данные';

    public function handle(): ActionResult
    {
        $broker = $this->getItem();
        
        if (!$broker instanceof Broker || $broker->account === null) {
            return $this->message('Нет аккаунта для синхронизации');
        }

        try {
            $portfolioService = app(PortfolioService::class);
            $positionsService = app(PositionsService::class);
            
            $portfolioService->refreshPortfolio($broker, $broker->account->id);
            $positionsService->refreshPositions($broker, $broker->account->id);
            
            return $this->message('Данные успешно обновлены');
        } catch (\Throwable $e) {
            return $this->message('Ошибка синхронизации: ' . $e->getMessage());
        }
    }
}
```

**Тесты:**
```php
// tests/Feature/MoonShine/Actions/SyncBrokerDataActionTest.php
test('sync action updates portfolio data')
test('sync action updates positions data')
test('sync action handles missing account')
test('sync action handles api errors')
```

---

### 5.4. Ресурс для позиций

**Задачи:**
- [ ] Создать модель `Position` (опционально, если нужно хранение в БД)
- [ ] Создать MoonShine ресурс для просмотра позиций
- [ ] Добавить фильтрацию и поиск

**Файлы для создания:**
```php
// app/MoonShine/Resources/PositionResource.php (опционально)
<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;

#[Group('Инвестиции')]
class PositionResource extends ModelResource
{
    protected string $model = \App\Models\Position::class;
    
    public function getTitle(): string
    {
        return 'Позиции';
    }
}
```

**Тесты:**
```php
// tests/Feature/MoonShine/PositionResourceTest.php
test('position resource lists positions')
test('position resource allows filtering')
test('position resource shows position details')
```

---

## 📅 Этап 6: Автоматическое обновление

**Длительность:** 2-3 дня

### 6.1. Команда для синхронизации

**Задачи:**
- [ ] Создать Artisan команду
- [ ] Добавить обработку ошибок
- [ ] Добавить логирование

**Файлы для создания:**
```php
// app/Console/Commands/SyncBrokerDataCommand.php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Broker;
use App\Services\BrokerGateway\Providers\TBank\Services\PortfolioService;
use App\Services\BrokerGateway\Providers\TBank\Services\PositionsService;
use Illuminate\Console\Command;

class SyncBrokerDataCommand extends Command
{
    protected $signature = 'broker:sync {--broker= : ID брокера} {--force : Принудительное обновление}';
    protected $description = 'Синхронизация данных портфеля с T-Bank API';

    public function handle(
        PortfolioService $portfolioService,
        PositionsService $positionsService,
    ): int {
        $brokerId = $this->option('broker');
        $force = $this->option('force');

        $query = $brokerId !== null 
            ? Broker::query()->where('id', $brokerId)
            : Broker::query()->whereHas('account');

        $brokers = $query->get();

        if ($brokers->isEmpty()) {
            $this->warn('Нет брокеров для синхронизации');
            return self::SUCCESS;
        }

        $this->info("Начало синхронизации {$brokers->count()} брокеров");

        foreach ($brokers as $broker) {
            if ($broker->account === null) {
                $this->warn("Брокер #{$broker->id} не имеет аккаунта");
                continue;
            }

            try {
                $this->line("Синхронизация брокера #{$broker->id}...");
                
                if ($force) {
                    $portfolioService->refreshPortfolio($broker, $broker->account->id);
                    $positionsService->refreshPositions($broker, $broker->account->id);
                } else {
                    $portfolioService->getPortfolio($broker, $broker->account->id);
                    $positionsService->getPositions($broker, $broker->account->id);
                }
                
                $this->info("✓ Брокер #{$broker->id} синхронизирован");
            } catch (\Throwable $e) {
                $this->error("✗ Ошибка брокера #{$broker->id}: {$e->getMessage()}");
                logger()->error('Broker sync failed', [
                    'broker_id' => $broker->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info('Синхронизация завершена');
        return self::SUCCESS;
    }
}
```

**Тесты:**
```php
// tests/Feature/Console/Commands/SyncBrokerDataCommandTest.php
test('sync command syncs all brokers')
test('sync command syncs specific broker when id provided')
test('sync command handles missing account')
test('sync command logs errors')
test('sync command force option refreshes cache')
```

---

### 6.2. Планировщик задач

**Задачи:**
- [ ] Настроить Scheduler в Kernel.php
- [ ] Добавить расписание каждые 2 минуты
- [ ] Протестировать выполнение

**Файлы для обновления:**
```php
// app/Console/Kernel.php (или app/Console/Commands если Laravel 12)
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Синхронизация данных каждые 2 минуты (чуть чаще TTL кэша)
        $schedule->command('broker:sync')
            ->everyTwoMinutes()
            ->withoutOverlapping()
            ->onOneServer();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
```

**Тесты:**
```php
// tests/Feature/Console/SchedulerTest.php
test('scheduler runs broker sync command every two minutes')
test('scheduler prevents overlapping executions')
```

---

### 6.3. Horizon для очередей (опционально)

**Задачи:**
- [ ] Настроить очередь для синхронизации
- [ ] Создать Job для фоновой синхронизации
- [ ] Настроить Horizon dashboard

**Файлы для создания:**
```php
// app/Jobs/SyncBrokerPortfolioJob.php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Broker;
use App\Services\BrokerGateway\Providers\TBank\Services\PortfolioService;
use App\Services\BrokerGateway\Providers\TBank\Services\PositionsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SyncBrokerPortfolioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Broker $broker,
    ) {}

    public function handle(
        PortfolioService $portfolioService,
        PositionsService $positionsService,
    ): void {
        if ($this->broker->account === null) {
            return;
        }

        $portfolioService->refreshPortfolio($this->broker, $this->broker->account->id);
        $positionsService->refreshPositions($this->broker, $this->broker->account->id);
    }
}
```

**Тесты:**
```php
// tests/Unit/Jobs/SyncBrokerPortfolioJobTest.php
test('job syncs portfolio data')
test('job syncs positions data')
test('job handles missing account')
test('job is queued correctly')
```

---

## 📅 Этап 7: Финальное тестирование и документация

**Длительность:** 2-3 дня

### 7.1. Интеграционное тестирование

**Задачи:**
- [ ] Написать end-to-end тесты
- [ ] Протестировать полный цикл
- [ ] Проверить производительность

**Файлы для создания:**
```php
// tests/Integration/FullWorkflowTest.php
test('full portfolio workflow from api to ui')
test('cache is properly invalidated after ttl')
test('multiple concurrent requests are handled')
test('api errors are gracefully handled')
test('scheduler runs sync command automatically')
```

---

### 7.2. Документация

**Задачи:**
- [ ] Обновить README.md
- [ ] Добавить документацию API
- [ ] Создать CHANGELOG.md
- [ ] Документировать конфигурацию

**Файлы для создания:**
```markdown
# docs/MVP/README.md
# docs/MVP/ARCHITECTURE.md
# docs/MVP/API.md
# CHANGELOG.md
```

---

### 7.3. Проверка кода

**Задачи:**
- [ ] Запустить `php -l` по всем измененным PHP-файлам
- [ ] Запустить Mago lint/analyze
- [ ] Исправить замечания
- [ ] Проверить покрытие тестами
- [ ] Code review

**Команды:**
```bash
php -l path/to/changed-file.php
make mago_ci_check
make mago_baseline_lint
```

---

## 📊 Итоговая сводка

### Длительность MVP: 21-30 дней

| Этап | Длительность | Результат |
|------|--------------|-----------|
| 1. Инфраструктура | 2-3 дня | Redis, тесты, конфиги |
| 2. Кэширование | 3-4 дня | PortfolioCacheRepository |
| 3. T-Bank API Client | 4-5 дней | REST клиенты, DTO |
| 4. Сервисный слой | 4-5 дней | Services с кэшированием |
| 5. MoonShine UI | 4-5 дней | Dashboard, страницы |
| 6. Автоматизация | 2-3 дня | Scheduler, Jobs |
| 7. Тестирование | 2-3 дня | E2E тесты, документация |

---

### Критерии готовности MVP

- [ ] Данные портфеля отображаются в MoonShine
- [ ] Данные позиций отображаются в MoonShine
- [ ] Кэширование работает с TTL 120 секунд
- [ ] Используется отдельный namespace переменных для брокерского кэша (`BROKER_GATEWAY_CACHE_TTL_SECONDS`)
- [ ] Автоматическое обновление каждые 2 минуты
- [ ] Ручная синхронизация через кнопку
- [ ] Покрытие тестами >80%
- [ ] Все тесты проходят
- [ ] `php -l` не показывает синтаксических ошибок в измененных PHP-файлах
- [ ] Mago не показывает ошибок

### Чеклист выравнивания с архитектурой

- [ ] Вся новая интеграционная логика находится в `laravel_app/app/Modules/BrokerGateway/*`
- [ ] В `laravel_app/app/Providers/*` только DI-композиция, без бизнес-логики
- [ ] MoonShine вызывает action/контракты и не содержит прямых API-клиентов
- [ ] Нет хардкода `tbank` в action-слое для выбора провайдера
- [ ] Legacy-слой используется только через явные adapter-границы

---

### Следующие шаги после MVP

1. Добавить получение операций (Operations API)
2. Реализовать стриминг данных (gRPC)
3. Добавить других брокеров
4. Расширить аналитику и отчёты
5. Создать публичное API
