<?php

declare(strict_types = 1);

namespace Tests\Feature\Broker;

use App\Models\Broker;
use App\Models\BrokerCredential;
use App\Models\User;
use App\Services\BrokerGateway\Profiles\BrokerProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class BrokerProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_profiles_using_broker_aware_runtime_settings(): void
    {
        config()->set('broker-providers.default_environment', 'sandbox');
        config()->set('broker-providers.providers.tbank', [
            'driver' => 'tbank',
            'default_environment' => 'sandbox',
            'default_connection_key' => 'sandbox',
            'connection_keys' => [
                'sandbox' => [
                    'provider' => 'tbank',
                    'base_url' => 'https://profiles.example.test/rest',
                    'timeout' => 7,
                ],
            ],
        ]);

        Http::fake([
            'https://profiles.example.test/rest/*' => Http::response([
                'accounts' => [
                    [
                        'id' => 'acc-1',
                        'name' => 'Main',
                        'type' => 'ACCOUNT_TYPE_TINKOFF',
                        'status' => 'ACCOUNT_STATUS_OPEN',
                        'accessLevel' => 'ACCOUNT_ACCESS_LEVEL_FULL_ACCESS',
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();
        $broker = Broker::withoutEvents(static fn(): Broker => Broker::query()->forceCreate([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'My broker profile',
            'provider_code' => 'tbank',
            'description' => null,
        ]));
        $token = hash('sha256', (string) microtime(true));

        BrokerCredential::query()->create([
            'broker_id' => (int) $broker->getKey(),
            'token' => $token,
            'expire_at' => null,
        ]);

        $profiles = app(BrokerProfileService::class)->profilesForUser($user);

        $this->assertCount(1, $profiles, 'Service must return one profile row from broker API response.');
        $this->assertSame('tbank', $profiles[0]['provider']);
        $this->assertSame('sandbox', $profiles[0]['environment']);
        $this->assertSame('acc-1', $profiles[0]['account_id']);
        $this->assertSame('Main', $profiles[0]['account_name']);
        $this->assertDatabaseMissing('accounts', [
            'broker_id' => (int) $broker->getKey(),
        ]);
    }

    public function test_it_returns_error_row_for_invalid_provider_runtime_config(): void
    {
        config()->set('broker-providers.providers.tbank', [
            'driver' => 'tbank',
            'default_environment' => 'sandbox',
            'default_connection_key' => 'sandbox',
            'connection_keys' => [
                'sandbox' => [
                    'provider' => 'tbank',
                    'base_url' => 'https://profiles.example.test/rest',
                    'timeout' => 7,
                ],
            ],
        ]);

        $user = User::factory()->create();
        Broker::withoutEvents(static fn(): Broker => Broker::query()->forceCreate([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'Broken broker',
            'provider_code' => 'missing-provider',
            'description' => null,
        ]));

        $profiles = app(BrokerProfileService::class)->profilesForUser($user);

        $this->assertCount(1, $profiles, 'Service must return one error row when runtime provider config is invalid.');
        $this->assertSame('missing-provider', $profiles[0]['provider']);
        $this->assertStringContainsString('Failed to resolve broker API settings', (string) $profiles[0]['error']);
    }

    public function test_profiles_for_user_does_not_mutate_existing_accounts(): void
    {
        config()->set('broker-providers.default_environment', 'sandbox');
        config()->set('broker-providers.providers.tbank', [
            'driver' => 'tbank',
            'default_environment' => 'sandbox',
            'default_connection_key' => 'sandbox',
            'connection_keys' => [
                'sandbox' => [
                    'provider' => 'tbank',
                    'base_url' => 'https://profiles.example.test/rest',
                    'timeout' => 7,
                ],
            ],
        ]);

        Http::fake([
            'https://profiles.example.test/rest/*' => Http::response([
                'accounts' => [
                    ['id' => 'external-1', 'name' => 'Main'],
                    ['id' => 'external-2', 'name' => 'Extra'],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();
        $broker = Broker::withoutEvents(static fn (): Broker => Broker::query()->forceCreate([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'My broker profile',
            'provider_code' => 'tbank',
            'description' => null,
        ]));
        $token = hash('sha256', (string) microtime(true));

        BrokerCredential::query()->create([
            'broker_id' => (int) $broker->getKey(),
            'token' => $token,
            'expire_at' => null,
        ]);

        DB::table('accounts')->insert([
            'broker_id' => (int) $broker->getKey(),
            'name' => 'Existing account',
            'external_account_id' => 'existing-id',
            'updated_at' => now(),
        ]);

        $before = DB::table('accounts')
            ->where('broker_id', (int) $broker->getKey())
            ->count();

        app(BrokerProfileService::class)->profilesForUser($user);

        $after = DB::table('accounts')
            ->where('broker_id', (int) $broker->getKey())
            ->count();

        $this->assertSame($before, $after, 'Read-path must not insert or delete rows in accounts.');
        $this->assertDatabaseHas('accounts', [
            'broker_id' => (int) $broker->getKey(),
            'external_account_id' => 'existing-id',
            'name' => 'Existing account',
        ]);
    }
}
