<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

abstract class TBankBaseRestClient
{
    protected function buildUrl(string $baseUrl, string $service, string $method): string
    {
        return sprintf(
            '%s/tinkoff.public.invest.api.contract.v1.%s/%s',
            rtrim($baseUrl, '/'),
            $service,
            $method,
        );
    }

    protected function postJson(
        string $url,
        array $payload,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        string $connectionExceptionMessage,
        string $requestExceptionMessage,
    ): Response {
        try {
            return $this->baseRequest($token, $timeout, $appName)->post($url, $payload);
        } catch (ConnectionException $e) {
            throw new RuntimeException($connectionExceptionMessage, previous: $e);
        } catch (Throwable $e) {
            throw new RuntimeException($requestExceptionMessage, previous: $e);
        }
    }

    protected function assertSuccessful(Response $response, string $failedStatusMessageTemplate): void
    {
        if ($response->successful()) {
            return;
        }

        throw new RuntimeException(sprintf($failedStatusMessageTemplate, $response->status()));
    }

    /**
     * @return array<string, mixed>
     */
    protected function assertArrayPayload(Response $response, string $invalidPayloadMessage): array
    {
        $payload = $response->json();

        if (!\is_array($payload)) {
            throw new RuntimeException($invalidPayloadMessage);
        }

        /** @var array<string, mixed> $payload */
        return $payload;
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
