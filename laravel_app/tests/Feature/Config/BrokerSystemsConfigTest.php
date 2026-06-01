<?php

declare(strict_types = 1);

namespace Tests\Feature\Config;

use Tests\TestCase;

final class BrokerSystemsConfigTest extends TestCase
{
    public function test_broker_systems_config_is_valid(): void
    {
        $config = config('broker-systems');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('enabled', $config);
        $this->assertArrayHasKey('systems', $config);
    }

    public function test_tbank_system_is_configured_in_separate_config(): void
    {
        $tbankConfig = config('broker-systems.systems.tbank');

        $this->assertIsArray($tbankConfig);
        $this->assertSame('tbank', $tbankConfig['driver']);
        $this->assertArrayHasKey('connection_keys', $tbankConfig);
        $this->assertArrayHasKey('prod', $tbankConfig['connection_keys']);
        $this->assertArrayHasKey('sandbox', $tbankConfig['connection_keys']);
    }

    public function test_tbank_connection_keys_have_required_schema(): void
    {
        $connectionKeys = config('broker-systems.systems.tbank.connection_keys');

        $this->assertIsArray($connectionKeys, 'connection_keys must be configured for tbank.');
        $this->assertArrayHasKey('prod', $connectionKeys, 'prod key is required.');
        $this->assertArrayHasKey('sandbox', $connectionKeys, 'sandbox key is required.');

        foreach ($connectionKeys as $connectionKey => $connectionConfig) {
            $this->assertIsString($connectionKey, 'Connection key name must be a string.');
            $this->assertIsArray($connectionConfig, "Connection key [$connectionKey] must be an array.");

            foreach (['provider', 'base_url', 'timeout'] as $requiredField) {
                $this->assertArrayHasKey(
                    $requiredField,
                    $connectionConfig,
                    "Missing [$requiredField] in connection key [$connectionKey].",
                );
            }

            $this->assertArrayNotHasKey('environment', $connectionConfig);
            $this->assertArrayNotHasKey('app_name', $connectionConfig);
            $this->assertArrayNotHasKey('label', $connectionConfig);
        }
    }

    public function test_enabled_broker_systems_keep_runtime_contract(): void
    {
        $enabledSystems = config('broker-systems.enabled');

        $this->assertIsArray($enabledSystems);
        $this->assertContains('tbank', $enabledSystems);
    }

    public function test_tbank_defaults_use_sandbox_for_non_production_app_env(): void
    {
        $tbankConfig = config('broker-systems.systems.tbank');

        $this->assertIsArray($tbankConfig);
        $this->assertSame('sandbox', $tbankConfig['default_connection_key'] ?? null);
        $this->assertSame('sandbox', $tbankConfig['default_environment'] ?? null);
    }

    public function test_tbank_defaults_use_prod_for_production_app_env(): void
    {
        $tbankConfig = $this->loadTbankConfigForAppEnv('production');

        $this->assertSame('prod', $tbankConfig['default_connection_key'] ?? null);
        $this->assertSame('prod', $tbankConfig['default_environment'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadTbankConfigForAppEnv(string $appEnv): array
    {
        $originalGetEnv = getenv('APP_ENV');
        $originalServerEnv = $_SERVER['APP_ENV'] ?? null;
        $originalPhpEnv = $_ENV['APP_ENV'] ?? null;

        putenv("APP_ENV={$appEnv}");
        $_SERVER['APP_ENV'] = $appEnv;
        $_ENV['APP_ENV'] = $appEnv;

        try {
            /** @var array<string, mixed> $config */
            $config = require base_path('config/broker-systems/tbank.php');

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
