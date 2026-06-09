<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Application;

use App\Models\Account;
use App\Models\Broker;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Adapters\LegacyTBankProviderConfigAdapter;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankUsersRestClient;
use App\Services\BrokerGateway\Credentials\BrokerCredentialResolver;
use Illuminate\Support\Facades\Log;

final class SyncTBankBrokerAccountsAction
{
    public function __construct(
        private readonly LegacyTBankProviderConfigAdapter $legacyProviderConfigAdapter,
        private readonly BrokerCredentialResolver $credentialResolver,
        private readonly TBankUsersRestClient $usersRestClient,
    ) {}

    public function execute(Broker $broker, ?string $fallbackAccountName = null): void
    {
        $providerConfig = $this->legacyProviderConfigAdapter->resolveForBroker($broker);
        $credential = $this->credentialResolver->resolveForEnvironment($broker, $providerConfig['environment']);
        $token = $this->credentialResolver->decryptToken($credential);
        $accountsPayload = $this->usersRestClient->getAccounts(
            baseUrl: $providerConfig['base_url'],
            token: $token,
            timeout: $providerConfig['timeout'],
            appName: $providerConfig['app_name'],
        );

        /** @var mixed $accountsRaw */
        $accountsRaw = data_get($accountsPayload, 'accounts', []);

        if (!\is_array($accountsRaw)) {
            Log::warning('SyncTBankBrokerAccountsAction received invalid accounts payload.', [
                'broker_id' => (int) $broker->getKey(),
                'endpoint' => 'UsersService/GetAccounts',
            ]);

            return;
        }

        $fallbackName = trim((string) ($fallbackAccountName ?? $broker->getAttribute('profile_name')));
        $syncedExternalAccountIds = [];

        foreach ($accountsRaw as $accountRaw) {
            if (!\is_array($accountRaw)) {
                continue;
            }

            $externalAccountId = trim((string) data_get($accountRaw, 'id', ''));

            if ($externalAccountId === '') {
                Log::warning('SyncTBankBrokerAccountsAction skipped account with empty id.', [
                    'broker_id' => (int) $broker->getKey(),
                    'endpoint' => 'UsersService/GetAccounts',
                ]);
                continue;
            }

            $accountName = trim((string) data_get($accountRaw, 'name', ''));

            Account::query()->updateOrCreate(
                [
                    'broker_id' => (int) $broker->getKey(),
                    'external_account_id' => $externalAccountId,
                ],
                ['name' => $accountName !== '' ? $accountName : $fallbackName],
            );

            $syncedExternalAccountIds[] = $externalAccountId;
        }

        if ($syncedExternalAccountIds === []) {
            Log::warning('SyncTBankBrokerAccountsAction got empty list of valid account identifiers.', [
                'broker_id' => (int) $broker->getKey(),
                'endpoint' => 'UsersService/GetAccounts',
            ]);
            return;
        }

        Account::query()
            ->where('broker_id', (int) $broker->getKey())
            ->whereNull('external_account_id')
            ->delete();
    }
}
