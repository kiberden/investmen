<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Application\Instruments;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments\AssetRestClient;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments\BondRestClient;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments\EtfRestClient;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments\ShareRestClient;
use RuntimeException;

final class InstrumentsGateway
{
    public function __construct(
        private readonly AssetRestClient $assetRestClient,
        private readonly BondRestClient $bondRestClient,
        private readonly EtfRestClient $etfRestClient,
        private readonly ShareRestClient $shareRestClient,
    ) {}

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function getInstrumentById(string $kind, string $idType, string $id, array $context): array
    {
        $resolved = $this->resolveContext($context);

        return match ($this->normalizeKind($kind)) {
            'asset' => $this->assetRestClient->getAssetBy(...$resolved, id: $id),
            'bond' => $this->bondRestClient->bondBy(...$resolved, idType: $idType, id: $id),
            'etf' => $this->etfRestClient->etfBy(...$resolved, idType: $idType, id: $id),
            'share' => $this->shareRestClient->shareBy(...$resolved, idType: $idType, id: $id),
        };
    }

    /**
     * @param array<string, mixed> $request
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function listInstruments(string $kind, array $request, array $context): array
    {
        $resolved = $this->resolveContext($context);

        return match ($this->normalizeKind($kind)) {
            'asset' => $this->assetRestClient->getAssets(...$resolved, request: $request),
            'bond' => $this->bondRestClient->bonds(...$resolved, request: $request),
            'etf' => $this->etfRestClient->etfs(...$resolved, request: $request),
            'share' => $this->shareRestClient->shares(...$resolved, request: $request),
        };
    }

    /**
     * @param array<string, mixed> $request
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function getAssetAnalytics(array $request, array $context): array
    {
        $resolved = $this->resolveContext($context);

        return [
            'fundamentals' => $this->assetRestClient->getAssetFundamentals(...$resolved, request: $request),
            'reports' => $this->assetRestClient->getAssetReports(...$resolved, request: $request),
        ];
    }

    /**
     * @param array<string, mixed> $request
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function getBondSchedule(array $request, array $context): array
    {
        $resolved = $this->resolveContext($context);

        return [
            'accrued_interests' => $this->bondRestClient->getAccruedInterests(...$resolved, request: $request),
            'coupons' => $this->bondRestClient->getBondCoupons(...$resolved, request: $request),
            'events' => $this->bondRestClient->getBondEvents(...$resolved, request: $request),
        ];
    }

    private function normalizeKind(string $kind): string
    {
        $normalizedKind = strtolower(trim($kind));

        if (\in_array($normalizedKind, ['asset', 'bond', 'etf', 'share'], true)) {
            return $normalizedKind;
        }

        throw new RuntimeException(sprintf('Unsupported instrument kind: %s', $kind));
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array{0: string, 1: string, 2: int, 3: string}
     */
    private function resolveContext(array $context): array
    {
        $baseUrl = data_get($context, 'base_url');
        $token = data_get($context, 'token');
        $timeout = data_get($context, 'timeout');
        $appName = data_get($context, 'app_name');

        if (!\is_string($baseUrl) || $baseUrl === '') {
            throw new RuntimeException('InstrumentsGateway requires non-empty base_url in context.');
        }

        if (!\is_string($token) || $token === '') {
            throw new RuntimeException('InstrumentsGateway requires non-empty token in context.');
        }

        if (!\is_int($timeout)) {
            throw new RuntimeException('InstrumentsGateway requires integer timeout in context.');
        }

        if (!\is_string($appName) || $appName === '') {
            throw new RuntimeException('InstrumentsGateway requires non-empty app_name in context.');
        }

        return [$baseUrl, $token, $timeout, $appName];
    }
}
