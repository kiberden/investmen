<?php

declare(strict_types = 1);

namespace App\Services\BrokerGateway\Profiles;

use App\Http\Resources\BrokerProfileResource;
use App\Models\Broker;
use App\Models\User;
use App\Modules\BrokerGateway\Core\BrokerProviderCodeResolver;
use App\Services\BrokerGateway\Credentials\BrokerCredentialResolver;
use App\Services\BrokerGateway\Factory\BrokerGatewayProviderFactory;
use RuntimeException;
use Throwable;

final class BrokerProfileService
{
    public function __construct(
        private readonly BrokerGatewayProviderFactory $providerFactory,
        private readonly BrokerProviderCodeResolver $providerCodeResolver,
        private readonly BrokerCredentialResolver $credentialResolver,
        private readonly BrokerProfileRestClient $brokerProfileRestClient,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function profilesForUser(User $user): array
    {
        /** @var list<array<string, mixed>> $profiles */
        $profiles = [];

        /** @var Broker $broker */
        foreach ($user->brokers()->orderBy('id')->get() as $broker) {
            $providerCode = $this->providerCodeResolver->resolveForBroker($broker);
            $profiles = [
                ...$profiles,
                ...$this->profilesForBroker($broker, $providerCode),
            ];
        }

        return $profiles;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function profilesForBroker(Broker $broker, string $providerCode): array
    {
        try {
            $provider = $this->providerFactory->make($providerCode);
            $credential = $this->credentialResolver->resolveForEnvironment($broker, $provider->getEnvironment());
            $token = $this->credentialResolver->decryptToken($credential);
            $accounts = $this->brokerProfileRestClient->fetchTBankAccounts(
                baseUrl: $provider->getBaseUrl(),
                token: $token,
                timeout: $provider->getTimeout(),
                appName: $provider->getAppName(),
            );
        } catch (Throwable $e) {
            return [
                BrokerProfileResource::make([
                    'broker_id' => (int) $broker->getKey(),
                    'broker_profile_name' => (string) $broker->getAttribute('profile_name'),
                    'provider' => $providerCode,
                    'error' => $this->messageForException($e),
                ])->resolve(),
            ];
        }

        $result = [];

        foreach ($accounts as $account) {
            $result[] = BrokerProfileResource::make([
                'broker_id' => (int) $broker->getKey(),
                'broker_profile_name' => (string) $broker->getAttribute('profile_name'),
                'provider' => $providerCode,
                'environment' => $provider->getEnvironment(),
                'account_id' => (string) data_get($account, 'id', ''),
                'account_name' => (string) data_get($account, 'name', ''),
                'account_type' => (string) data_get($account, 'type', ''),
                'account_status' => (string) data_get($account, 'status', ''),
                'access_level' => (string) data_get($account, 'accessLevel', ''),
                'opened_at' => data_get($account, 'openedDate'),
                'closed_at' => data_get($account, 'closedDate'),
            ])->resolve();
        }

        if ($result === []) {
            $result[] = BrokerProfileResource::make([
                'broker_id' => (int) $broker->getKey(),
                'broker_profile_name' => (string) $broker->getAttribute('profile_name'),
                'provider' => $providerCode,
                'environment' => $provider->getEnvironment(),
                'error' => 'Профили не найдены в ответе брокера.',
            ])->resolve();
        }

        return $result;
    }

    private function messageForException(Throwable $e): string
    {
        if ($e instanceof RuntimeException) {
            return $e->getMessage();
        }

        return 'Не удалось получить данные профилей брокера.';
    }
}
