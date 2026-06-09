<?php

declare(strict_types = 1);

namespace App\Services\BrokerGateway\Profiles;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankBaseRestClient;
use RuntimeException;

final class BrokerProfileRestClient extends TBankBaseRestClient
{
    /**
     * @return list<array<string, mixed>>
     */
    public function fetchTBankAccounts(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
    ): array {
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'UsersService', 'GetAccounts'),
            [],
            $token,
            $timeout,
            $appName,
            'Failed to connect to broker profile endpoint.',
            'Failed to fetch broker profiles.',
        );

        $this->assertSuccessful($response, 'Broker profile request failed with status %d.');

        $accounts = data_get($response->json(), 'accounts');

        if (!\is_array($accounts)) {
            return [];
        }

        return array_values(array_filter($accounts, static fn(mixed $account): bool => \is_array($account)));
    }
}
