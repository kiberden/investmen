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
        $this->assertArrayHasKey('environments', $tbankProvider);
    }

    public function test_tbank_environments_are_configured(): void
    {
        $environments = config('broker-providers.providers.tbank.environments');

        $this->assertIsArray($environments);
        $this->assertArrayHasKey('prod', $environments);
        $this->assertArrayHasKey('sandbox', $environments);

        foreach (['prod', 'sandbox'] as $environment) {
            $this->assertArrayHasKey('base_url', $environments[$environment]);
            $this->assertArrayHasKey('timeout', $environments[$environment]);
            $this->assertArrayHasKey('app_name', $environments[$environment]);

            $this->assertIsString($environments[$environment]['base_url']);
            $this->assertNotSame('', $environments[$environment]['base_url']);
            $this->assertIsInt($environments[$environment]['timeout']);
            $this->assertGreaterThan(0, $environments[$environment]['timeout']);
            $this->assertIsString($environments[$environment]['app_name']);
            $this->assertNotSame('', $environments[$environment]['app_name']);
        }
    }
}
