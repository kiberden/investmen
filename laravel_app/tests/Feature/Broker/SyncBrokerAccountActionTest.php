<?php

declare(strict_types = 1);

namespace Tests\Feature\Broker;

use App\Actions\Broker\SyncBrokerAccountAction;
use App\Models\Broker;
use App\Models\BrokerCredential;
use App\Models\User;
use App\Modules\BrokerGateway\Core\BrokerProviderRegistry;
use App\Modules\BrokerGateway\Core\Contracts\BrokerProvider;
use App\Modules\BrokerGateway\Core\Contracts\BrokerStreamClient;
use App\Modules\BrokerGateway\Core\DTO\SyncBrokerAccountResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class SyncBrokerAccountActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_persisted_provider_code_instead_of_first_configured_provider(): void
    {
        config()->set('broker-providers.providers', [
            'first-provider' => ['driver' => 'first-provider'],
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
        $broker = Broker::withoutEvents(static fn(): Broker => Broker::query()->forceCreate([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'Sandbox profile',
            'provider_code' => 'custom-provider',
            'description' => null,
        ]));

        app(SyncBrokerAccountAction::class)->execute($broker);

        $this->assertDatabaseHas('accounts', [
            'broker_id' => (int) $broker->getKey(),
            'name' => 'Main account',
        ]);
    }

    public function test_it_logs_warning_when_provider_code_is_not_registered(): void
    {
        config()->set('broker-providers.providers', [
            'tbank' => ['driver' => 'tbank'],
        ]);

        app()->instance(BrokerProviderRegistry::class, new BrokerProviderRegistry([]));
        Log::spy();

        $user = User::factory()->create();
        $broker = Broker::withoutEvents(static fn(): Broker => Broker::query()->forceCreate([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'Invalid provider profile',
            'provider_code' => 'missing-provider',
            'description' => null,
        ]));

        app(SyncBrokerAccountAction::class)->execute($broker);

        $this->assertDatabaseMissing('accounts', [
            'broker_id' => (int) $broker->getKey(),
        ]);

        Log::shouldHaveReceived('warning')->once();
    }

    public function test_it_uses_tbank_multi_account_sync_instead_of_single_account_upsert(): void
    {
        config()->set('broker-providers.providers', [
            'tbank' => [
                'driver' => 'tbank',
                'default_environment' => 'sandbox',
                'default_connection_key' => 'sandbox',
                'connection_keys' => [
                    'sandbox' => [
                        'provider' => 'tbank',
                        'base_url' => 'https://accounts-sync.example.test/rest',
                        'timeout' => 10,
                    ],
                ],
            ],
        ]);
        config()->set('broker-providers.default_environment', 'sandbox');

        Http::fake([
            'https://accounts-sync.example.test/rest/*' => Http::response([
                'accounts' => [
                    ['id' => 'acc-1', 'name' => 'Main account'],
                    ['id' => 'acc-2', 'name' => 'Second account'],
                ],
            ], 200),
        ]);

        $provider = new class implements BrokerProvider {
            public function code(): string
            {
                return 'tbank';
            }

            public function syncAccount(Broker $broker): SyncBrokerAccountResult
            {
                return new SyncBrokerAccountResult('Fallback profile');
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
        $broker = Broker::withoutEvents(static fn(): Broker => Broker::query()->forceCreate([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'Sandbox profile',
            'provider_code' => 'tbank',
            'description' => null,
        ]));
        $token = hash('sha256', (string) microtime(true));

        BrokerCredential::query()->create([
            'broker_id' => (int) $broker->getKey(),
            'token' => $token,
            'expire_at' => null,
        ]);

        app(SyncBrokerAccountAction::class)->execute($broker);

        $this->assertDatabaseHas('accounts', [
            'broker_id' => (int) $broker->getKey(),
            'external_account_id' => 'acc-1',
            'name' => 'Main account',
        ]);
        $this->assertDatabaseHas('accounts', [
            'broker_id' => (int) $broker->getKey(),
            'external_account_id' => 'acc-2',
            'name' => 'Second account',
        ]);
    }
}
