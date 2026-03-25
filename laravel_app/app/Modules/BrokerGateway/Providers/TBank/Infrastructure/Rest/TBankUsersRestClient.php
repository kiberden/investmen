<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * REST-клиент для endpoint UsersService провайдера T-Bank.
 */
final class TBankUsersRestClient
{
    /**
     * Запрашивает профиль пользователя из метода UsersService/GetInfo.
     *
     * @return array<string, mixed>
     */
    public function getInfo(string $baseUrl, #[\SensitiveParameter] string $token, int $timeout, string $appName): array
    {
        try {
            /** @var Response $response */
            $response = $this->baseRequest($token, $timeout, $appName)->post($this->getInfoUrl($baseUrl), []);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Failed to connect to T-Bank UsersService/GetInfo endpoint.', previous: $e);
        } catch (Throwable $e) {
            throw new RuntimeException('Failed to fetch T-Bank user info.', previous: $e);
        }

        if (!$response->successful()) {
            throw new RuntimeException(sprintf(
                'T-Bank UsersService/GetInfo failed with status %d.',
                $response->status(),
            ));
        }

        $payload = $response->json();

        if (!\is_array($payload)) {
            throw new RuntimeException('T-Bank UsersService/GetInfo returned invalid payload.');
        }

        /** @var array<string, mixed> $payload */
        return $payload;
    }

    /**
     * Формирует полный URL метода UsersService/GetInfo.
     */
    private function getInfoUrl(string $baseUrl): string
    {
        return rtrim($baseUrl, '/').'/tinkoff.public.invest.api.contract.v1.UsersService/GetInfo';
    }

    /**
     * Создает базовый HTTP-запрос с авторизацией и заголовками приложения.
     */
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
