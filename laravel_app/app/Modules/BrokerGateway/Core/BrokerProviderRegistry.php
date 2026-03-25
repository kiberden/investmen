<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Core;

use App\Modules\BrokerGateway\Core\Contracts\BrokerProvider;
use InvalidArgumentException;

final class BrokerProviderRegistry
{
    /**
     * @var array<string, BrokerProvider>
     */
    private array $providersByCode = [];

    /**
     * @param iterable<BrokerProvider> $providers
     */
    public function __construct(iterable $providers)
    {
        foreach ($providers as $provider) {
            $this->providersByCode[strtolower($provider->code())] = $provider;
        }
    }

    public function forCode(string $code): BrokerProvider
    {
        $normalizedCode = strtolower(trim($code));
        $provider = $this->providersByCode[$normalizedCode] ?? null;

        if ($provider instanceof BrokerProvider) {
            return $provider;
        }

        throw new InvalidArgumentException(sprintf('Broker provider "%s" is not registered.', $code));
    }
}
