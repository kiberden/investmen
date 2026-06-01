<?php

declare(strict_types = 1);

namespace App\Services\BrokerGateway\Providers\TBank;

use App\Services\BrokerGateway\Contracts\BrokerGatewayProviderInterface;
use InvalidArgumentException;

/**
 * Провайдер доступа к настройкам подключения T-Bank через connection keys.
 */
final class TBankProvider implements BrokerGatewayProviderInterface
{
    /**
     * @param array<string, mixed> $providerConfig
     */
    public function __construct(
        private readonly array $providerConfig,
        private readonly string $environment,
    ) {}

    /**
     * Возвращает код провайдера.
     */
    public function getCode(): string
    {
        return 'tbank';
    }

    /**
     * Возвращает отображаемое имя провайдера.
     */
    public function getName(): string
    {
        return (string) ( $this->providerConfig['name'] ?? 'T-Bank' );
    }

    /**
     * Возвращает активный ключ подключения (prod/sandbox).
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Возвращает базовый URL API для активного ключа подключения.
     */
    public function getBaseUrl(): string
    {
        return (string) $this->getConnectionConfigValue('base_url');
    }

    /**
     * Возвращает таймаут HTTP-запросов для активного ключа подключения.
     */
    public function getTimeout(): int
    {
        return (int) $this->getConnectionConfigValue('timeout', 10);
    }

    /**
     * Возвращает имя приложения для upstream-запросов.
     */
    public function getAppName(): string
    {
        return 'investman';
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

    /**
     * Читает значение из текущего connection key.
     */
    private function getConnectionConfigValue(string $key, mixed $default = null): mixed
    {
        $connectionKeys = $this->readArrayValue($this->providerConfig, 'connection_keys');

        if (!\array_key_exists($this->environment, $connectionKeys)) {
            throw new InvalidArgumentException(sprintf(
                'Connection key "%s" is not configured for provider "%s".',
                $this->environment,
                $this->getCode(),
            ));
        }

        $connectionConfig = $this->readArrayValue(
            $connectionKeys,
            $this->environment,
            sprintf(
                'Invalid connection key format for provider "%s" and key "%s".',
                $this->getCode(),
                $this->environment,
            ),
        );

        return $connectionConfig[$key] ?? $default;
    }

    /**
     * Безопасно читает массив из конфигурации и нормализует ключи.
     *
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
