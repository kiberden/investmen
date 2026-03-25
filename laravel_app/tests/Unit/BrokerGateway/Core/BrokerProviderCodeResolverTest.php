<?php

declare(strict_types = 1);

namespace Tests\Unit\BrokerGateway\Core;

use App\Models\Broker;
use App\Modules\BrokerGateway\Core\BrokerProviderCodeResolver;
use RuntimeException;
use Tests\TestCase;

final class BrokerProviderCodeResolverTest extends TestCase
{
    public function test_it_resolves_provider_from_model_attribute(): void
    {
        $broker = new Broker;
        $broker->setAttribute('provider_code', 'TBANK');

        $resolver = app(BrokerProviderCodeResolver::class);

        $this->assertSame('tbank', $resolver->resolveForBroker($broker));
    }

    public function test_it_resolves_provider_from_first_configured_provider(): void
    {
        config()->set('broker-providers.providers', [
            'alphabroker' => ['driver' => 'alpha'],
            'tbank' => ['driver' => 'tbank'],
        ]);

        $broker = new Broker;
        $resolver = app(BrokerProviderCodeResolver::class);

        $this->assertSame('alphabroker', $resolver->resolveForBroker($broker));
    }

    public function test_it_throws_when_no_provider_configured(): void
    {
        config()->set('broker-providers.providers', []);

        $broker = new Broker;
        $resolver = app(BrokerProviderCodeResolver::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No broker providers configured.');

        $resolver->resolveForBroker($broker);
    }
}
