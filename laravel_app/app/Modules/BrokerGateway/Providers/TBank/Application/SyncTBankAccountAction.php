<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Application;

use App\Models\Broker;
use App\Modules\BrokerGateway\Core\DTO\SyncBrokerAccountResult;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Adapters\LegacyTBankProviderConfigAdapter;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankUsersRestClient;
use App\Services\BrokerGateway\Credentials\BrokerCredentialResolver;

/**
 * Сценарий синхронизации имени брокерского аккаунта через REST API T-Bank.
 */
final class SyncTBankAccountAction
{
    /**
     * @param LegacyTBankProviderConfigAdapter $legacyProviderConfigAdapter Явная adapter-граница к legacy factory.
     * @param BrokerCredentialResolver $credentialResolver Резолвер и дешифратор учетных данных.
     * @param TBankUsersRestClient $usersRestClient HTTP-клиент endpoint UsersService/GetInfo.
     */
    public function __construct(
        private readonly LegacyTBankProviderConfigAdapter $legacyProviderConfigAdapter,
        private readonly BrokerCredentialResolver $credentialResolver,
        private readonly TBankUsersRestClient $usersRestClient,
    ) {}

    /**
     * Выполняет запрос к T-Bank и возвращает данные для синхронизации аккаунта.
     */
    public function execute(Broker $broker): SyncBrokerAccountResult
    {
        $providerConfig = $this->legacyProviderConfigAdapter->resolve();
        $credential = $this->credentialResolver->resolveForEnvironment($broker, $providerConfig['environment']);
        $token = $this->credentialResolver->decryptToken($credential);
        $userInfo = $this->usersRestClient->getInfo(
            baseUrl: $providerConfig['base_url'],
            token: $token,
            timeout: $providerConfig['timeout'],
            appName: $providerConfig['app_name'],
        );

        $name = data_get($userInfo, 'name');

        if (!\is_string($name) || $name === '') {
            $name = (string) $broker->getAttribute('profile_name');
        }

        return new SyncBrokerAccountResult($name);
    }
}
