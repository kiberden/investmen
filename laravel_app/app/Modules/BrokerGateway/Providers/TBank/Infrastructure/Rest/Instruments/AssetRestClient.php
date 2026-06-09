<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankBaseRestClient;
use Illuminate\Http\Client\Response;

final class AssetRestClient extends TBankBaseRestClient
{
    /**
     * @return array<string, mixed>
     */
    public function getAssetBy(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        string $id,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'InstrumentsService', 'GetAssetBy'),
            ['id' => $id],
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank InstrumentsService/GetAssetBy endpoint.',
            'Failed to fetch T-Bank asset by id.',
        );

        $this->assertSuccessful($response, 'T-Bank InstrumentsService/GetAssetBy failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank InstrumentsService/GetAssetBy returned invalid payload.');
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function getAssetFundamentals(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        array $request,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'InstrumentsService', 'GetAssetFundamentals'),
            $request,
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank InstrumentsService/GetAssetFundamentals endpoint.',
            'Failed to fetch T-Bank asset fundamentals.',
        );

        $this->assertSuccessful($response, 'T-Bank InstrumentsService/GetAssetFundamentals failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank InstrumentsService/GetAssetFundamentals returned invalid payload.');
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function getAssetReports(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        array $request,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'InstrumentsService', 'GetAssetReports'),
            $request,
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank InstrumentsService/GetAssetReports endpoint.',
            'Failed to fetch T-Bank asset reports.',
        );

        $this->assertSuccessful($response, 'T-Bank InstrumentsService/GetAssetReports failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank InstrumentsService/GetAssetReports returned invalid payload.');
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function getAssets(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        array $request,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'InstrumentsService', 'GetAssets'),
            $request,
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank InstrumentsService/GetAssets endpoint.',
            'Failed to fetch T-Bank assets.',
        );

        $this->assertSuccessful($response, 'T-Bank InstrumentsService/GetAssets failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank InstrumentsService/GetAssets returned invalid payload.');
    }
}
