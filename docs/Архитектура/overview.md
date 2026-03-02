# Архитектура

## Основные документы

- [[provider-integration|Подключение провайдеров и T-Bank first]]
- `c4-broker-provider-tbank.puml` (PlantUML источник C4)

## Текущий фокус

- provider factory для выбора брокерского провайдера;
- доступ к подключениям только в рамках профиля пользователя;
- получение `token/secret` только через `brokerCredentials`;
- сервис-провайдер Laravel для DI-регистрации;
- базовые API-методы (`portfolio`, `positions`, `operations`);
- заглушки для расширенных/stream методов;
- short-term cache для разгрузки внешнего API.

## C4 (кратко)

```mermaid
flowchart LR
    U["оператор"] --> M["MoonShine Admin UI"]
    M --> B["Laravel Backend"]
    B --> A["BrokerAccessService"]
    B --> F["BrokerGatewayProviderFactory"]
    F --> CR["BrokerCredentialResolver"]
    F --> T["TBankProvider"]
    T --> O["TBankOperationsClient"]
    O --> G["T-Bank Gateway API"]
    B --> BR[("brokers")]
    CR --> BC[("brokerCredentials")]
    B --> C["PortfolioCacheRepository"]
    C --> R[(Redis)]
    B --> D[(PostgreSQL)]
```
