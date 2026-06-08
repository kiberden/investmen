<?php

declare(strict_types = 1);

namespace Tests\Feature\Config;

use Tests\TestCase;

final class BrokerProvidersConfigTest extends TestCase
{
    public function test_broker_providers_config_is_valid(): void
    {
        $config = config('broker-providers');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('default_environment', $config);
        $this->assertArrayHasKey('providers', $config);
        $this->assertIsArray($config['providers']);
    }

    public function test_tbank_provider_has_required_configuration(): void
    {
        $tbankProvider = config('broker-providers.providers.tbank');

        $this->assertIsArray($tbankProvider);
        $this->assertSame('tbank', $tbankProvider['driver']);
        $this->assertArrayHasKey('default_environment', $tbankProvider);
        $this->assertArrayHasKey('default_connection_key', $tbankProvider);
        $this->assertArrayHasKey('connection_keys', $tbankProvider);
    }

    public function test_tbank_connection_keys_are_configured_with_minimal_schema(): void
    {
        $connectionKeys = config('broker-providers.providers.tbank.connection_keys');

        $this->assertIsArray($connectionKeys);
        $this->assertArrayHasKey('prod', $connectionKeys);
        $this->assertArrayHasKey('sandbox', $connectionKeys);

        foreach (['prod', 'sandbox'] as $connectionKey) {
            $this->assertArrayHasKey('base_url', $connectionKeys[$connectionKey]);
            $this->assertArrayHasKey('timeout', $connectionKeys[$connectionKey]);
            $this->assertArrayHasKey('provider', $connectionKeys[$connectionKey]);

            $this->assertIsString($connectionKeys[$connectionKey]['base_url']);
            $this->assertNotSame('', $connectionKeys[$connectionKey]['base_url']);
            $this->assertIsInt($connectionKeys[$connectionKey]['timeout']);
            $this->assertGreaterThan(0, $connectionKeys[$connectionKey]['timeout']);

            $this->assertArrayNotHasKey('app_name', $connectionKeys[$connectionKey]);
            $this->assertArrayNotHasKey('environment', $connectionKeys[$connectionKey]);
            $this->assertArrayNotHasKey('label', $connectionKeys[$connectionKey]);
        }
    }

    public function test_connection_keys_are_aggregated_for_enabled_providers(): void
    {
        $connectionKeys = config('broker-providers.connection_keys');

        $this->assertIsArray($connectionKeys);
        $this->assertArrayHasKey('prod', $connectionKeys);
        $this->assertArrayHasKey('sandbox', $connectionKeys);

        foreach (['prod', 'sandbox'] as $connectionKey) {
            $this->assertSame(
                'tbank',
                $connectionKeys[$connectionKey]['provider'] ?? null,
                "Connection key [$connectionKey] must be linked to tbank provider.",
            );

            $this->assertArrayHasKey('base_url', $connectionKeys[$connectionKey]);
            $this->assertArrayHasKey('timeout', $connectionKeys[$connectionKey]);
        }
    }

    public function test_default_environment_uses_sandbox_for_non_production_app_env(): void
    {
        $config = config('broker-providers');

        $this->assertIsArray($config);
        $this->assertSame('sandbox', $config['default_environment'] ?? null);
    }

    public function test_default_environment_uses_prod_for_production_app_env(): void
    {
        $config = $this->loadBrokerProvidersConfigForAppEnv('production');

        $this->assertSame('prod', $config['default_environment'] ?? null);
    }

    public function test_providers_match_enabled_broker_systems(): void
    {
        $config = config('broker-providers');
        $enabledSystems = config('broker-systems.enabled');

        $this->assertIsArray($config);
        $this->assertIsArray($enabledSystems);
        $this->assertArrayHasKey('providers', $config);

        $providerCodes = array_keys($config['providers']);
        sort($providerCodes);

        $normalizedEnabledSystems = array_map(
            static fn(mixed $code): string => \is_string($code) ? strtolower(trim($code)) : '',
            $enabledSystems,
        );
        $normalizedEnabledSystems = array_values(array_filter(
            $normalizedEnabledSystems,
            static fn(string $code): bool => $code !== '',
        ));
        sort($normalizedEnabledSystems);

        $this->assertSame(
            $normalizedEnabledSystems,
            $providerCodes,
            'Configured providers must match enabled broker systems.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function loadBrokerProvidersConfigForAppEnv(string $appEnv): array
    {
        $originalGetEnv = getenv('APP_ENV');
        $originalServerEnv = $_SERVER['APP_ENV'] ?? null;
        $originalPhpEnv = $_ENV['APP_ENV'] ?? null;

        putenv("APP_ENV={$appEnv}");
        $_SERVER['APP_ENV'] = $appEnv;
        $_ENV['APP_ENV'] = $appEnv;

        try {
            /** @var array<string, mixed> $config */
            $config = require base_path('config/broker-providers.php');

            return $config;
        } finally {
            if ($originalGetEnv === false) {
                putenv('APP_ENV');
            } else {
                putenv("APP_ENV={$originalGetEnv}");
            }

            if ($originalServerEnv === null) {
                unset($_SERVER['APP_ENV']);
            } else {
                $_SERVER['APP_ENV'] = $originalServerEnv;
            }

            if ($originalPhpEnv === null) {
                unset($_ENV['APP_ENV']);
            } else {
                $_ENV['APP_ENV'] = $originalPhpEnv;
            }
        }
    }
}
