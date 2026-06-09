<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest;

use Illuminate\Http\Client\Response;

/**
 * REST-клиент для endpoint UsersService провайдера T-Bank.
 */
final class TBankUsersRestClient extends TBankBaseRestClient
{
    /**
     * Запрашивает профиль пользователя из метода UsersService/GetInfo.
     *
     * @return array<string, mixed>
     */
    public function getInfo(string $baseUrl, #[\SensitiveParameter] string $token, int $timeout, string $appName): array
    {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'UsersService', 'GetInfo'),
            [],
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank UsersService/GetInfo endpoint.',
            'Failed to fetch T-Bank user info.',
        );

        $this->assertSuccessful($response, 'T-Bank UsersService/GetInfo failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank UsersService/GetInfo returned invalid payload.');
    }

    /**
     * Запрашивает список инвестиционных счетов пользователя из метода UsersService/GetAccounts.
     *
     * @return array<string, mixed>
     */
    public function getAccounts(string $baseUrl, #[\SensitiveParameter] string $token, int $timeout, string $appName): array
    {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'UsersService', 'GetAccounts'),
            [],
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank UsersService/GetAccounts endpoint.',
            'Failed to fetch T-Bank user accounts.',
        );

        $this->assertSuccessful($response, 'T-Bank UsersService/GetAccounts failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank UsersService/GetAccounts returned invalid payload.');
    }

    /**
     * Запрашивает список банковских счетов пользователя из метода UsersService/GetBankAccounts.
     *
     * @return array<string, mixed>
     */
    public function getBankAccounts(string $baseUrl, #[\SensitiveParameter] string $token, int $timeout, string $appName): array
    {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'UsersService', 'GetBankAccounts'),
            [],
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank UsersService/GetBankAccounts endpoint.',
            'Failed to fetch T-Bank user bank accounts.',
        );

        $this->assertSuccessful($response, 'T-Bank UsersService/GetBankAccounts failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank UsersService/GetBankAccounts returned invalid payload.');
    }

    /**
     * Запрашивает тариф пользователя из метода UsersService/GetUserTariff.
     *
     * @return array<string, mixed>
     */
    public function getUserTariff(string $baseUrl, #[\SensitiveParameter] string $token, int $timeout, string $appName): array
    {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'UsersService', 'GetUserTariff'),
            [],
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank UsersService/GetUserTariff endpoint.',
            'Failed to fetch T-Bank user tariff.',
        );

        $this->assertSuccessful($response, 'T-Bank UsersService/GetUserTariff failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank UsersService/GetUserTariff returned invalid payload.');
    }
}
