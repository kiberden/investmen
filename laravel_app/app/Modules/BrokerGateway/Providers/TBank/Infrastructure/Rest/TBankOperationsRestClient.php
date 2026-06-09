<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest;

use Illuminate\Http\Client\Response;

/**
 * REST-клиент для endpoint OperationsService провайдера T-Bank.
 */
final class TBankOperationsRestClient extends TBankBaseRestClient
{
    /**
     * Получает портфель по идентификатору счета через OperationsService/GetPortfolio.
     *
     * @return array<string, mixed>
     */
    public function getPortfolio(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        string $accountId,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'OperationsService', 'GetPortfolio'),
            ['accountId' => $accountId],
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank OperationsService/GetPortfolio endpoint.',
            'Failed to fetch T-Bank portfolio.',
        );

        $this->assertSuccessful($response, 'T-Bank OperationsService/GetPortfolio failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank OperationsService/GetPortfolio returned invalid payload.');
    }

    /**
     * Получает позиции по идентификатору счета через OperationsService/GetPositions.
     *
     * @return array<string, mixed>
     */
    public function getPositions(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        string $accountId,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'OperationsService', 'GetPositions'),
            ['accountId' => $accountId],
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank OperationsService/GetPositions endpoint.',
            'Failed to fetch T-Bank positions.',
        );

        $this->assertSuccessful($response, 'T-Bank OperationsService/GetPositions failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank OperationsService/GetPositions returned invalid payload.');
    }

    /**
     * Получает операции с пагинацией через OperationsService/GetOperationsByCursor.
     *
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function getOperationsByCursor(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        array $request,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'OperationsService', 'GetOperationsByCursor'),
            $request,
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank OperationsService/GetOperationsByCursor endpoint.',
            'Failed to fetch T-Bank operations by cursor.',
        );

        $this->assertSuccessful($response, 'T-Bank OperationsService/GetOperationsByCursor failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank OperationsService/GetOperationsByCursor returned invalid payload.');
    }
}
