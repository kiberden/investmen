<?php

declare(strict_types = 1);

namespace App\Services\BrokerGateway\Providers\TBank;

use App\Services\BrokerGateway\Contracts\BrokerGatewayProviderInterface;
use InvalidArgumentException;

final class TBankProvider implements BrokerGatewayProviderInterface
{
    /**
     * @param array<string, mixed> $providerConfig
     */
    public function __construct(
        private readonly array $providerConfig,
        private readonly string $environment,
    ) {}

    public function getCode(): string
    {
        return 'tbank';
    }

    public function getName(): string
    {
        return (string) ( $this->providerConfig['name'] ?? 'T-Bank' );
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getBaseUrl(): string
    {
        return (string) $this->getEnvironmentConfigValue('base_url');
    }

    public function getTimeout(): int
    {
        return (int) $this->getEnvironmentConfigValue('timeout', 10);
    }

    public function getAppName(): string
    {
        return (string) $this->getEnvironmentConfigValue('app_name', 'investman');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->getCode(),
            'name' => $this->getName(),
            'environment' => $this->getEnvironment(),
            'base_url' => $this->getBaseUrl(),
            'timeout' => $this->getTimeout(),
            'app_name' => $this->getAppName(),
        ];
    }

    private function getEnvironmentConfigValue(string $key, mixed $default = null): mixed
    {
        $environments = $this->readArrayValue($this->providerConfig, 'environments');

        if (!\array_key_exists($this->environment, $environments)) {
            throw new InvalidArgumentException(sprintf(
                'Environment "%s" is not configured for provider "%s".',
                $this->environment,
                $this->getCode(),
            ));
        }

        $environmentConfig = $this->readArrayValue(
            $environments,
            $this->environment,
            sprintf(
                'Invalid configuration format for provider "%s" and environment "%s".',
                $this->getCode(),
                $this->environment,
            ),
        );

        return $environmentConfig[$key] ?? $default;
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function readArrayValue(array $config, string $key, ?string $errorMessage = null): array
    {
        $value = $config[$key] ?? [];

        if (!\is_array($value)) {
            throw new InvalidArgumentException(
                $errorMessage ?? sprintf(
                    'Configuration value "%s" for provider "%s" must be an array.',
                    $key,
                    $this->getCode(),
                ),
            );
        }

        $normalizedValue = [];

        foreach ($value as $valueKey => $item) {
            $normalizedValue[(string) $valueKey] = $item;
        }

        return $normalizedValue;
    }
}
