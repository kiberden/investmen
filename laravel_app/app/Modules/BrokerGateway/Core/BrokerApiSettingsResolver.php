<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Core;

use App\Models\Broker;
use App\Services\BrokerGateway\Factory\BrokerGatewayProviderFactory;
use InvalidArgumentException;
use RuntimeException;

/**
 * Резолвер runtime-настроек API для конкретной записи брокера.
 */
readonly final class BrokerApiSettingsResolver
{
    /**
     * @param BrokerProviderCodeResolver $providerCodeResolver Резолвер кода провайдера из брокера/конфига.
     * @param BrokerGatewayProviderFactory $providerFactory Фабрика runtime-провайдера.
     */
    public function __construct(
        private BrokerProviderCodeResolver $providerCodeResolver,
        private BrokerGatewayProviderFactory $providerFactory,
    ) {}

    /**
     * Возвращает согласованный runtime-набор API-настроек для выбранного брокера.
     *
     * @return array{
     *     provider_code: string,
     *     environment: string,
     *     base_url: string,
     *     timeout: int,
     *     app_name: string
     * }
     */
    public function resolveForBroker(Broker $broker): array
    {
        $providerCode = $this->providerCodeResolver->resolveForBroker($broker);

        try {
            $provider = $this->providerFactory->make($providerCode);
            return [
                'provider_code' => $providerCode,
                'environment' => $provider->getEnvironment(),
                'base_url' => $provider->getBaseUrl(),
                'timeout' => $provider->getTimeout(),
                'app_name' => $provider->getAppName(),
            ];
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(
                sprintf('Failed to resolve broker API settings for provider "%s".', $providerCode),
                previous: $e,
            );
        }
    }
}
