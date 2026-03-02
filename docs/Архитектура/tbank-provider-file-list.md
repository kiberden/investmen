# Перечень файлов для подключения к брокерскому шлюзу (T-Bank)

Ниже перечислены ключевые файлы, реализующие подключение к банковскому шлюзу на примере T-Bank:

1. `laravel_app/config/broker-providers.php`
   - Конфигурация провайдеров брокерского шлюза.
   - Для `tbank` заданы окружения `prod` и `sandbox` (`base_url`, `timeout`, `app_name`).

2. `laravel_app/app/Providers/BrokerGatewayServiceProvider.php`
   - Регистрация конфигурации и DI-привязок для слоя брокерского шлюза.
   - Регистрация singleton-фабрики `BrokerGatewayProviderFactory`.

3. `laravel_app/app/Services/BrokerGateway/Contracts/BrokerGatewayProviderInterface.php`
   - Контракт провайдера подключения (код, окружение, endpoint и runtime-параметры).

4. `laravel_app/app/Services/BrokerGateway/Factory/BrokerGatewayProviderFactory.php`
   - Фабрика получения провайдера по коду (`tbank`) и окружению.
   - Поддерживает дефолтное окружение из `config`.

5. `laravel_app/app/Services/BrokerGateway/Providers/TBank/TBankProvider.php`
   - Реализация провайдера подключения T-Bank.
   - Выдает параметры подключения для выбранного окружения.

6. `laravel_app/app/Services/BrokerGateway/Access/BrokerAccessService.php`
   - Проверка доступа пользователя к подключению (`broker`) и выборка доступных подключений.

7. `laravel_app/app/Services/BrokerGateway/Credentials/BrokerCredentialResolver.php`
   - Безопасное получение активных учетных данных подключения из `broker_credentials`.
   - Получение token/secret в виде строк для последующего использования провайдером.

8. `laravel_app/config/app.php`
   - Подключение `App\Providers\BrokerGatewayServiceProvider` в список провайдеров приложения.

