<?php

declare(strict_types = 1);

namespace App\Services\BrokerGateway\Profiles;

use App\Http\Resources\BrokerProfileResource;
use App\Models\Broker;
use App\Models\User;
use App\Modules\BrokerGateway\Core\BrokerApiSettingsResolver;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankUsersRestClient;
use App\Services\BrokerGateway\Credentials\BrokerCredentialResolver;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Сервис получения профилей брокерских счетов для API-ресурсов.
 */
final class BrokerProfileService
{
    public function __construct(
        private readonly BrokerApiSettingsResolver $apiSettingsResolver,
        private readonly BrokerCredentialResolver $credentialResolver,
        private readonly TBankUsersRestClient $usersRestClient,
    ) {}

    /**
     * Возвращает агрегированные профили по всем брокерам пользователя.
     *
     * @return list<array<string, mixed>>
     */
    public function profilesForUser(User $user): array
    {
        /** @var list<array<string, mixed>> $profiles */
        $profiles = [];

        /** @var Broker $broker */
        foreach ($user->brokers()->orderBy('id')->get() as $broker) {
            $profiles = [
                ...$profiles,
                ...$this->profilesForBroker($broker),
            ];
        }

        return $profiles;
    }

    /**
     * Получает профили для конкретного брокера через broker-aware runtime-настройки.
     *
     * @return list<array<string, mixed>>
     */
    private function profilesForBroker(Broker $broker): array
    {
        try {
            $settings = $this->apiSettingsResolver->resolveForBroker($broker);
            $credential = $this->credentialResolver->resolveForEnvironment($broker, $settings['environment']);
            $token = $this->credentialResolver->decryptToken($credential);
            $accountsPayload = $this->usersRestClient->getAccounts(
                baseUrl: $settings['base_url'],
                token: $token,
                timeout: $settings['timeout'],
                appName: $settings['app_name'],
            );
            $accounts = data_get($accountsPayload, 'accounts');

            if (!\is_array($accounts)) {
                $accounts = [];
            }
        } catch (Throwable $e) {
            $providerCode = $this->providerCodeOrUnknown($broker);
            Log::error('BrokerProfileService failed to fetch broker profiles.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => $providerCode,
                'endpoint' => 'UsersService/GetAccounts',
                'error' => $e->getMessage(),
            ]);

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
                'provider' => $settings['provider_code'],
                'environment' => $settings['environment'],
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
            Log::warning('BrokerProfileService got empty accounts payload from broker.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => $settings['provider_code'],
                'endpoint' => 'UsersService/GetAccounts',
            ]);

            $result[] = BrokerProfileResource::make([
                'broker_id' => (int) $broker->getKey(),
                'broker_profile_name' => (string) $broker->getAttribute('profile_name'),
                'provider' => $settings['provider_code'],
                'environment' => $settings['environment'],
                'error' => 'Профили не найдены в ответе брокера.',
            ])->resolve();
        }

        return $result;
    }

    /**
     * Нормализует сообщение ошибки для API-ответа профилей.
     */
    private function messageForException(Throwable $e): string
    {
        if ($e instanceof RuntimeException) {
            return $e->getMessage();
        }

        return 'Не удалось получить данные профилей брокера.';
    }

    /**
     * Возвращает код провайдера из брокера либо "unknown" для ошибок резолва.
     */
    private function providerCodeOrUnknown(Broker $broker): string
    {
        $providerCode = $broker->getAttribute('provider_code');

        if (\is_string($providerCode) && $providerCode !== '') {
            return strtolower(trim($providerCode));
        }

        return 'unknown';
    }
}
