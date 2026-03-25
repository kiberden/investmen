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
        $this->assertArrayHasKey('environments', $tbankConfig);
        $this->assertArrayHasKey('prod', $tbankConfig['environments']);
        $this->assertArrayHasKey('sandbox', $tbankConfig['environments']);
    }
}
