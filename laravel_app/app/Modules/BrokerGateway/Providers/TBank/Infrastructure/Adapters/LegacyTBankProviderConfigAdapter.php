<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Adapters;

use App\Services\BrokerGateway\Factory\BrokerGatewayProviderFactory;

/**
 * Явная adapter-граница к legacy factory.
 */
final readonly class LegacyTBankProviderConfigAdapter
{
    public function __construct(
        private BrokerGatewayProviderFactory $providerFactory,
    ) {}

    /**
     * @return array{
     *     environment: string,
     *     base_url: string,
     *     timeout: int,
     *     app_name: string
     * }
     */
    public function resolve(): array
    {
        $provider = $this->providerFactory->make('tbank');

        return [
            'environment' => $provider->getEnvironment(),
            'base_url' => $provider->getBaseUrl(),
            'timeout' => $provider->getTimeout(),
            'app_name' => $provider->getAppName(),
        ];
    }
}
