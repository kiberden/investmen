<?php

declare(strict_types = 1);

namespace Tests\Unit\BrokerGateway\Providers\TBank\Infrastructure\Adapters;

use App\Models\Broker;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Adapters\LegacyTBankProviderConfigAdapter;
use RuntimeException;
use Tests\TestCase;

final class LegacyTBankProviderConfigAdapterTest extends TestCase
{
    public function test_legacy_resolve_keeps_tbank_provider_behavior(): void
    {
        config()->set('broker-providers.providers', [
            'alphabroker' => [
                'driver' => 'alpha',
                'default_environment' => 'sandbox',
                'default_connection_key' => 'sandbox',
                'connection_keys' => [
                    'sandbox' => [
                        'provider' => 'alpha',
                        'base_url' => 'https://alpha.example.test/rest',
                        'timeout' => 5,
                    ],
                ],
            ],
            'tbank' => [
                'driver' => 'tbank',
                'default_environment' => 'sandbox',
                'default_connection_key' => 'sandbox',
                'connection_keys' => [
                    'sandbox' => [
                        'provider' => 'tbank',
                        'base_url' => 'https://legacy-tbank.example.test/rest',
                        'timeout' => 13,
                    ],
                ],
            ],
        ]);

        $config = app(LegacyTBankProviderConfigAdapter::class)->resolve();

        $this->assertSame(
            'https://legacy-tbank.example.test/rest',
            $config['base_url'],
            'Legacy resolve() must always keep TBank-specific behavior.',
        );
        $this->assertSame(13, $config['timeout']);
    }

    public function test_it_resolves_runtime_configuration_for_broker(): void
    {
        config()->set('broker-providers.default_environment', 'sandbox');
        config()->set('broker-providers.providers.tbank', [
            'driver' => 'tbank',
            'default_environment' => 'sandbox',
            'default_connection_key' => 'sandbox',
            'connection_keys' => [
                'sandbox' => [
                    'provider' => 'tbank',
                    'base_url' => 'https://adapter.example.test/rest',
                    'timeout' => 19,
                ],
            ],
        ]);

        $broker = new Broker;
        $broker->setAttribute('provider_code', 'tbank');

        $config = app(LegacyTBankProviderConfigAdapter::class)->resolveForBroker($broker);

        $this->assertSame('sandbox', $config['environment'], 'Adapter must return runtime environment from broker-aware resolver.');
        $this->assertSame('https://adapter.example.test/rest', $config['base_url']);
        $this->assertSame(19, $config['timeout']);
        $this->assertSame('investman', $config['app_name']);
    }

    public function test_it_throws_for_invalid_provider_configuration(): void
    {
        config()->set('broker-providers.providers.tbank', [
            'driver' => 'tbank',
            'default_environment' => 'sandbox',
            'default_connection_key' => 'sandbox',
            'connection_keys' => [
                'sandbox' => [
                    'provider' => 'tbank',
                    'base_url' => 'https://adapter.example.test/rest',
                    'timeout' => 10,
                ],
            ],
        ]);

        $broker = new Broker;
        $broker->setAttribute('provider_code', 'missing-provider');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to resolve broker API settings for provider "missing-provider".');

        app(LegacyTBankProviderConfigAdapter::class)->resolveForBroker($broker);
    }
}
