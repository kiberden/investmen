<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankBaseRestClient;
use Illuminate\Http\Client\Response;

final class ShareRestClient extends TBankBaseRestClient
{
    /**
     * @return array<string, mixed>
     */
    public function shareBy(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        string $idType,
        string $id,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'InstrumentsService', 'ShareBy'),
            [
                'idType' => $idType,
                'id' => $id,
            ],
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank InstrumentsService/ShareBy endpoint.',
            'Failed to fetch T-Bank share by id.',
        );

        $this->assertSuccessful($response, 'T-Bank InstrumentsService/ShareBy failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank InstrumentsService/ShareBy returned invalid payload.');
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function shares(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        array $request,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'InstrumentsService', 'Shares'),
            $request,
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank InstrumentsService/Shares endpoint.',
            'Failed to fetch T-Bank shares.',
        );

        $this->assertSuccessful($response, 'T-Bank InstrumentsService/Shares failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank InstrumentsService/Shares returned invalid payload.');
    }
}
