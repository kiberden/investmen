<?php

declare(strict_types = 1);

namespace App\Services\BrokerGateway\Profiles;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class BrokerProfileRestClient
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
        try {
            $response = $this->baseRequest($token, $timeout, $appName)->post($this->accountsUrl($baseUrl), []);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Failed to connect to broker profile endpoint.', previous: $e);
        } catch (Throwable $e) {
            throw new RuntimeException('Failed to fetch broker profiles.', previous: $e);
        }

        if (!$response->successful()) {
            throw new RuntimeException(sprintf('Broker profile request failed with status %d.', $response->status()));
        }

        $accounts = data_get($response->json(), 'accounts');

        if (!\is_array($accounts)) {
            return [];
        }

        return array_values(array_filter($accounts, static fn(mixed $account): bool => \is_array($account)));
    }

    private function accountsUrl(string $baseUrl): string
    {
        return rtrim($baseUrl, '/').'/tinkoff.public.invest.api.contract.v1.UsersService/GetAccounts';
    }

    private function baseRequest(#[\SensitiveParameter] string $token, int $timeout, string $appName): PendingRequest
    {
        return Http::asJson()
            ->acceptJson()
            ->timeout(max(1, $timeout))
            ->withToken($token)
            ->withHeaders([
                'x-app-name' => $appName,
            ]);
    }
}
