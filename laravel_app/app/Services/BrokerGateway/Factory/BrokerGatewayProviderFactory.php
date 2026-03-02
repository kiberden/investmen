<?php

declare(strict_types = 1);

namespace App\Services\BrokerGateway\Factory;

use App\Services\BrokerGateway\Contracts\BrokerGatewayProviderInterface;
use App\Services\BrokerGateway\Providers\TBank\TBankProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

final class BrokerGatewayProviderFactory
{
    public function __construct(
        private readonly Repository $config,
        private readonly Container $container,
    ) {}

    public function make(string $providerCode, ?string $environment = null): BrokerGatewayProviderInterface
    {
        $providerCode = strtolower(trim($providerCode));
        $providerConfig = $this->providerConfig($providerCode);
        $resolvedEnvironment = $this->resolveEnvironment($providerConfig, $environment);

        return match ($providerCode) {
            'tbank' => $this->container->make(TBankProvider::class, [
                'providerConfig' => $providerConfig,
                'environment' => $resolvedEnvironment,
            ]),
            default => throw new InvalidArgumentException(sprintf('Provider "%s" is not supported.', $providerCode)),
        };
    }

    /**
     * @return list<string>
     */
    public function supportedProviders(): array
    {
        return array_values(array_map('strval', array_keys($this->providersConfig())));
    }

    /**
     * @return array<string, mixed>
     */
    private function providerConfig(string $providerCode): array
    {
        $providerConfig = $this->providersConfig()[$providerCode] ?? null;

        if (!\is_array($providerConfig)) {
            throw new InvalidArgumentException(sprintf('Provider "%s" is not configured.', $providerCode));
        }

        return $this->normalizeConfigArray($providerConfig);
    }

    /**
     * @param array<string, mixed> $providerConfig
     */
    private function resolveEnvironment(array $providerConfig, ?string $environment): string
    {
        if (\is_string($environment) && $environment !== '') {
            return $environment;
        }

        $providerDefaultEnvironment = $this->readStringValue($providerConfig, 'default_environment');
        if ($providerDefaultEnvironment !== null && $providerDefaultEnvironment !== '') {
            return $providerDefaultEnvironment;
        }

        /** @var mixed $globalDefaultEnvironment */
        $globalDefaultEnvironment = $this->config->get('broker-providers.default_environment', 'sandbox');

        return \is_string($globalDefaultEnvironment) && $globalDefaultEnvironment !== ''
            ? $globalDefaultEnvironment
            : 'sandbox';
    }

    /**
     * @return array<string, mixed>
     */
    private function providersConfig(): array
    {
        /** @var mixed $providers */
        $providers = $this->config->get('broker-providers.providers', []);

        if (!\is_array($providers)) {
            return [];
        }

        return $this->normalizeConfigArray($providers);
    }

    /**
     * @param array<array-key, mixed> $config
     * @return array<string, mixed>
     */
    private function normalizeConfigArray(array $config): array
    {
        $normalizedConfig = [];

        foreach ($config as $key => $value) {
            $normalizedConfig[(string) $key] = $value;
        }

        return $normalizedConfig;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function readStringValue(array $config, string $key): ?string
    {
        $value = $config[$key] ?? null;

        return \is_string($value) ? $value : null;
    }
}
