<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankBaseRestClient;
use Illuminate\Http\Client\Response;

final class EtfRestClient extends TBankBaseRestClient
{
    /**
     * @return array<string, mixed>
     */
    public function etfBy(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        string $idType,
        string $id,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'InstrumentsService', 'EtfBy'),
            [
                'idType' => $idType,
                'id' => $id,
            ],
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank InstrumentsService/EtfBy endpoint.',
            'Failed to fetch T-Bank ETF by id.',
        );

        $this->assertSuccessful($response, 'T-Bank InstrumentsService/EtfBy failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank InstrumentsService/EtfBy returned invalid payload.');
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function etfs(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        array $request,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'InstrumentsService', 'Etfs'),
            $request,
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank InstrumentsService/Etfs endpoint.',
            'Failed to fetch T-Bank ETFs.',
        );

        $this->assertSuccessful($response, 'T-Bank InstrumentsService/Etfs failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank InstrumentsService/Etfs returned invalid payload.');
    }
}
