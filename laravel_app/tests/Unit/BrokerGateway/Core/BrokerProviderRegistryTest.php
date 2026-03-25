<?php

declare(strict_types = 1);

namespace Tests\Unit\BrokerGateway\Core;

use App\Models\Broker;
use App\Modules\BrokerGateway\Core\BrokerProviderRegistry;
use App\Modules\BrokerGateway\Core\Contracts\BrokerProvider;
use App\Modules\BrokerGateway\Core\Contracts\BrokerStreamClient;
use App\Modules\BrokerGateway\Core\DTO\SyncBrokerAccountResult;
use InvalidArgumentException;
use Tests\TestCase;

final class BrokerProviderRegistryTest extends TestCase
{
    public function test_it_returns_registered_provider_by_case_insensitive_code(): void
    {
        $provider = new class implements BrokerProvider {
            public function code(): string
            {
                return 'tbank';
            }

            public function syncAccount(Broker $broker): SyncBrokerAccountResult
            {
                return new SyncBrokerAccountResult('test');
            }

            public function streamClient(): BrokerStreamClient
            {
                return new class implements BrokerStreamClient {
                    public function run(): void
                    {
                    }
                };
            }
        };

        $registry = new BrokerProviderRegistry([$provider]);

        $resolvedProvider = $registry->forCode('TBANK');

        $this->assertSame($provider, $resolvedProvider);
    }

    public function test_it_throws_for_unknown_provider(): void
    {
        $registry = new BrokerProviderRegistry([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Broker provider "missing" is not registered.');

        $registry->forCode('missing');
    }
}
