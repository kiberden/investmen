<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankBaseRestClient;
use Illuminate\Http\Client\Response;

final class BondRestClient extends TBankBaseRestClient
{
    /**
     * @return array<string, mixed>
     */
    public function bondBy(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        string $idType,
        string $id,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'InstrumentsService', 'BondBy'),
            [
                'idType' => $idType,
                'id' => $id,
            ],
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank InstrumentsService/BondBy endpoint.',
            'Failed to fetch T-Bank bond by id.',
        );

        $this->assertSuccessful($response, 'T-Bank InstrumentsService/BondBy failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank InstrumentsService/BondBy returned invalid payload.');
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function bonds(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        array $request,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'InstrumentsService', 'Bonds'),
            $request,
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank InstrumentsService/Bonds endpoint.',
            'Failed to fetch T-Bank bonds.',
        );

        $this->assertSuccessful($response, 'T-Bank InstrumentsService/Bonds failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank InstrumentsService/Bonds returned invalid payload.');
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function getAccruedInterests(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        array $request,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'InstrumentsService', 'GetAccruedInterests'),
            $request,
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank InstrumentsService/GetAccruedInterests endpoint.',
            'Failed to fetch T-Bank accrued interests.',
        );

        $this->assertSuccessful($response, 'T-Bank InstrumentsService/GetAccruedInterests failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank InstrumentsService/GetAccruedInterests returned invalid payload.');
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function getBondCoupons(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        array $request,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'InstrumentsService', 'GetBondCoupons'),
            $request,
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank InstrumentsService/GetBondCoupons endpoint.',
            'Failed to fetch T-Bank bond coupons.',
        );

        $this->assertSuccessful($response, 'T-Bank InstrumentsService/GetBondCoupons failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank InstrumentsService/GetBondCoupons returned invalid payload.');
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function getBondEvents(
        string $baseUrl,
        #[\SensitiveParameter] string $token,
        int $timeout,
        string $appName,
        array $request,
    ): array {
        /** @var Response $response */
        $response = $this->postJson(
            $this->buildUrl($baseUrl, 'InstrumentsService', 'GetBondEvents'),
            $request,
            $token,
            $timeout,
            $appName,
            'Failed to connect to T-Bank InstrumentsService/GetBondEvents endpoint.',
            'Failed to fetch T-Bank bond events.',
        );

        $this->assertSuccessful($response, 'T-Bank InstrumentsService/GetBondEvents failed with status %d.');

        return $this->assertArrayPayload($response, 'T-Bank InstrumentsService/GetBondEvents returned invalid payload.');
    }
}
