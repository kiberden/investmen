<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Core;

use App\Models\Broker;
use Illuminate\Contracts\Config\Repository;
use RuntimeException;

final readonly class BrokerProviderCodeResolver
{
    public function __construct(
        private Repository $config,
    ) {}

    public function resolveForBroker(Broker $broker): string
    {
        $providerCode = $broker->getAttribute('provider_code');

        if (\is_string($providerCode) && $providerCode !== '') {
            return strtolower(trim($providerCode));
        }

        /** @var mixed $providers */
        $providers = $this->config->get('broker-providers.providers', []);
        if (!\is_array($providers) || $providers === []) {
            throw new RuntimeException('No broker providers configured.');
        }

        $defaultProviderCode = array_key_first($providers);

        if (!\is_string($defaultProviderCode) || $defaultProviderCode === '') {
            throw new RuntimeException('Unable to resolve broker provider code from config.');
        }

        return strtolower(trim($defaultProviderCode));
    }
}
