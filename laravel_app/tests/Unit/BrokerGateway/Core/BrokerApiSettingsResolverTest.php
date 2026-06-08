<?php

declare(strict_types = 1);

namespace Tests\Unit\BrokerGateway\Core;

use App\Models\Broker;
use App\Modules\BrokerGateway\Core\BrokerApiSettingsResolver;
use RuntimeException;
use Tests\TestCase;

final class BrokerApiSettingsResolverTest extends TestCase
{
    public function test_it_resolves_settings_from_broker_provider_code(): void
    {
        config()->set('broker-providers.default_environment', 'sandbox');
        config()->set('broker-providers.providers.tbank', [
            'driver' => 'tbank',
            'default_environment' => 'sandbox',
            'default_connection_key' => 'sandbox',
            'connection_keys' => [
                'sandbox' => [
                    'provider' => 'tbank',
                    'base_url' => 'https://sandbox.example.test/rest',
                    'timeout' => 15,
                ],
            ],
        ]);

        $broker = new Broker;
        $broker->setAttribute('provider_code', 'TBANK');

        $settings = app(BrokerApiSettingsResolver::class)->resolveForBroker($broker);

        $this->assertSame('tbank', $settings['provider_code'], 'Resolver must normalize provider_code to lowercase.');
        $this->assertSame('sandbox', $settings['environment'], 'Resolver must keep provider environment contract.');
        $this->assertSame('https://sandbox.example.test/rest', $settings['base_url']);
        $this->assertSame(15, $settings['timeout']);
        $this->assertSame('investman', $settings['app_name']);
    }

    public function test_it_uses_fallback_provider_when_broker_provider_is_empty(): void
    {
        config()->set('broker-providers.default_environment', 'sandbox');
        config()->set('broker-providers.providers', [
            'tbank' => [
                'driver' => 'tbank',
                'default_environment' => 'sandbox',
                'default_connection_key' => 'sandbox',
                'connection_keys' => [
                    'sandbox' => [
                        'provider' => 'tbank',
                        'base_url' => 'https://fallback.example.test/rest',
                        'timeout' => 12,
                    ],
                ],
            ],
        ]);

        $settings = app(BrokerApiSettingsResolver::class)->resolveForBroker(new Broker);

        $this->assertSame('tbank', $settings['provider_code'], 'Resolver must fallback to first configured provider.');
        $this->assertSame('https://fallback.example.test/rest', $settings['base_url']);
    }

    public function test_it_falls_back_to_sandbox_when_default_environment_is_invalid(): void
    {
        config()->set('broker-providers.default_environment', null);
        config()->set('broker-providers.providers.tbank', [
            'driver' => 'tbank',
            'default_environment' => '',
            'default_connection_key' => 'sandbox',
            'connection_keys' => [
                'sandbox' => [
                    'provider' => 'tbank',
                    'base_url' => 'https://sandbox-fallback.example.test/rest',
                    'timeout' => 11,
                ],
            ],
        ]);

        $broker = new Broker;
        $broker->setAttribute('provider_code', 'tbank');

        $settings = app(BrokerApiSettingsResolver::class)->resolveForBroker($broker);

        $this->assertSame('sandbox', $settings['environment'], 'Resolver must fallback to sandbox when default_environment is invalid.');
        $this->assertSame('https://sandbox-fallback.example.test/rest', $settings['base_url']);
    }

    public function test_it_throws_runtime_exception_for_unsupported_provider(): void
    {
        config()->set('broker-providers.providers.tbank', [
            'driver' => 'tbank',
            'default_environment' => 'sandbox',
            'default_connection_key' => 'sandbox',
            'connection_keys' => [
                'sandbox' => [
                    'provider' => 'tbank',
                    'base_url' => 'https://sandbox.example.test/rest',
                    'timeout' => 10,
                ],
            ],
        ]);

        $broker = new Broker;
        $broker->setAttribute('provider_code', 'unknown-provider');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to resolve broker API settings for provider "unknown-provider".');

        app(BrokerApiSettingsResolver::class)->resolveForBroker($broker);
    }

    public function test_it_wraps_invalid_provider_runtime_config_into_runtime_exception(): void
    {
        config()->set('broker-providers.providers.tbank', [
            'driver' => 'tbank',
            'default_environment' => 'sandbox',
            'default_connection_key' => 'sandbox',
            'connection_keys' => 'invalid',
        ]);

        $broker = new Broker;
        $broker->setAttribute('provider_code', 'tbank');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to resolve broker API settings for provider "tbank".');

        app(BrokerApiSettingsResolver::class)->resolveForBroker($broker);
    }

    public function test_it_maps_app_env_contract_to_environment(): void
    {
        $productionSettings = $this->resolveUsingAppEnv('production');
        $localSettings = $this->resolveUsingAppEnv('local');

        $this->assertSame('prod', $productionSettings['environment'], 'APP_ENV=production must resolve provider environment to "prod".');
        $this->assertSame(
            'https://invest-public-api.tbank.ru/rest',
            $productionSettings['base_url'],
            'APP_ENV=production must use production TBank base URL.',
        );
        $this->assertSame('sandbox', $localSettings['environment'], 'Non-production APP_ENV must resolve environment to "sandbox".');
        $this->assertSame(
            'https://sandbox-invest-public-api.tbank.ru/rest',
            $localSettings['base_url'],
            'Non-production APP_ENV must use sandbox TBank base URL.',
        );
    }

    /**
     * @return array{
     *     provider_code: string,
     *     environment: string,
     *     base_url: string,
     *     timeout: int,
     *     app_name: string
     * }
     */
    private function resolveUsingAppEnv(string $appEnv): array
    {
        $previousAppEnv = getenv('APP_ENV');
        putenv(sprintf('APP_ENV=%s', $appEnv));
        $_ENV['APP_ENV'] = $appEnv;
        $_SERVER['APP_ENV'] = $appEnv;

        $tbankConfig = require config_path('broker-systems/tbank.php');
        config()->set('broker-providers.default_environment', $tbankConfig['default_environment']);
        config()->set('broker-providers.providers.tbank', $tbankConfig);

        $broker = new Broker;
        $broker->setAttribute('provider_code', 'tbank');
        $settings = app(BrokerApiSettingsResolver::class)->resolveForBroker($broker);

        if ($previousAppEnv === false) {
            putenv('APP_ENV');
            unset($_ENV['APP_ENV'], $_SERVER['APP_ENV']);
        } else {
            putenv(sprintf('APP_ENV=%s', $previousAppEnv));
            $_ENV['APP_ENV'] = $previousAppEnv;
            $_SERVER['APP_ENV'] = $previousAppEnv;
        }

        return $settings;
    }
}
