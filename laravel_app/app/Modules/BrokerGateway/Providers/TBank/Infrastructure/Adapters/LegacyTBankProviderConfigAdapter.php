<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Adapters;

use App\Models\Broker;
use App\Modules\BrokerGateway\Core\BrokerApiSettingsResolver;

/**
 * Явная adapter-граница к legacy factory.
 */
final readonly class LegacyTBankProviderConfigAdapter
{
    public function __construct(
        private BrokerApiSettingsResolver $apiSettingsResolver,
    ) {}

    /**
     * @return array{
     *     environment: string,
     *     base_url: string,
     *     timeout: int,
     *     app_name: string
     * }
     */
    public function resolveForBroker(Broker $broker): array
    {
        $settings = $this->apiSettingsResolver->resolveForBroker($broker);

        return [
            'environment' => $settings['environment'],
            'base_url' => $settings['base_url'],
            'timeout' => $settings['timeout'],
            'app_name' => $settings['app_name'],
        ];
    }

    /**
     * Legacy-метод без контекста брокера для обратной совместимости.
     *
     * @deprecated Используйте resolveForBroker().
     *
     * @return array{
     *     environment: string,
     *     base_url: string,
     *     timeout: int,
     *     app_name: string
     * }
     */
    public function resolve(): array
    {
        $broker = new Broker;
        $broker->setAttribute('provider_code', 'tbank');

        return $this->resolveForBroker($broker);
    }
}
