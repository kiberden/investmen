<?php

declare(strict_types = 1);

namespace Tests\Feature\Broker;

use App\Actions\Broker\SyncBrokerAccountAction;
use App\Models\Broker;
use App\Models\User;
use App\Modules\BrokerGateway\Core\BrokerProviderRegistry;
use App\Modules\BrokerGateway\Core\Contracts\BrokerProvider;
use App\Modules\BrokerGateway\Core\Contracts\BrokerStreamClient;
use App\Modules\BrokerGateway\Core\DTO\SyncBrokerAccountResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SyncBrokerAccountActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_resolved_provider_code_and_creates_account(): void
    {
        config()->set('broker-providers.providers', [
            'custom-provider' => ['driver' => 'custom-provider'],
        ]);

        $provider = new class implements BrokerProvider {
            public function code(): string
            {
                return 'custom-provider';
            }

            public function syncAccount(Broker $broker): SyncBrokerAccountResult
            {
                return new SyncBrokerAccountResult('Main account');
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

        app()->instance(BrokerProviderRegistry::class, new BrokerProviderRegistry([$provider]));

        $user = User::factory()->create();
        $broker = Broker::query()->create([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'Sandbox profile',
            'description' => null,
        ]);

        app(SyncBrokerAccountAction::class)->execute($broker);

        $this->assertDatabaseHas('accounts', [
            'broker_id' => (int) $broker->getKey(),
            'name' => 'Main account',
        ]);
    }
}
