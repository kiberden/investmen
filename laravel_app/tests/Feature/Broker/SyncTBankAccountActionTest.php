<?php

declare(strict_types = 1);

namespace Tests\Feature\Broker;

use App\Models\Broker;
use App\Models\BrokerCredential;
use App\Models\User;
use App\Modules\BrokerGateway\Providers\TBank\Application\SyncTBankAccountAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

final class SyncTBankAccountActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_name_from_tbank_users_service(): void
    {
        config()->set('broker-providers.providers.tbank', [
            'driver' => 'tbank',
            'default_environment' => 'sandbox',
            'default_connection_key' => 'sandbox',
            'connection_keys' => [
                'sandbox' => [
                    'provider' => 'tbank',
                    'base_url' => 'https://example.test/rest',
                    'timeout' => 10,
                ],
            ],
        ]);

        Http::fake([
            'https://example.test/rest/*' => Http::response(['name' => 'Trader name'], 200),
        ]);

        $user = User::factory()->create();
        $broker = Broker::withoutEvents(static fn(): Broker => Broker::query()->forceCreate([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'Fallback profile',
            'provider_code' => 'tbank',
            'description' => null,
        ]));
        $token = hash('sha256', (string) microtime(true));

        BrokerCredential::query()->create([
            'broker_id' => (int) $broker->getKey(),
            'token' => $token,
            'expire_at' => null,
        ]);

        $result = app(SyncTBankAccountAction::class)->execute($broker);

        $this->assertSame('Trader name', $result->accountName());
        Http::assertSent(static fn($request): bool => str_starts_with($request->url(), 'https://example.test/rest/'));
    }

    public function test_it_falls_back_to_profile_name_when_api_does_not_return_name(): void
    {
        config()->set('broker-providers.providers.tbank', [
            'driver' => 'tbank',
            'default_environment' => 'sandbox',
            'default_connection_key' => 'sandbox',
            'connection_keys' => [
                'sandbox' => [
                    'provider' => 'tbank',
                    'base_url' => 'https://example.test/rest',
                    'timeout' => 10,
                ],
            ],
        ]);

        Http::fake([
            'https://example.test/rest/*' => Http::response(['name' => ''], 200),
        ]);

        $user = User::factory()->create();
        $broker = Broker::withoutEvents(static fn(): Broker => Broker::query()->forceCreate([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'Profile fallback',
            'provider_code' => 'tbank',
            'description' => null,
        ]));
        $token = hash('sha256', (string) microtime(true));

        BrokerCredential::query()->create([
            'broker_id' => (int) $broker->getKey(),
            'token' => $token,
            'expire_at' => null,
        ]);

        $result = app(SyncTBankAccountAction::class)->execute($broker);

        $this->assertSame('Profile fallback', $result->accountName());
    }

    public function test_it_throws_runtime_exception_for_invalid_runtime_provider_code(): void
    {
        config()->set('broker-providers.providers.tbank', [
            'driver' => 'tbank',
            'default_environment' => 'sandbox',
            'default_connection_key' => 'sandbox',
            'connection_keys' => [
                'sandbox' => [
                    'provider' => 'tbank',
                    'base_url' => 'https://example.test/rest',
                    'timeout' => 10,
                ],
            ],
        ]);

        $user = User::factory()->create();
        $broker = Broker::withoutEvents(static fn(): Broker => Broker::query()->forceCreate([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'Broken broker',
            'provider_code' => 'missing-provider',
            'description' => null,
        ]));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to resolve broker API settings for provider "missing-provider".');

        app(SyncTBankAccountAction::class)->execute($broker);
    }
}
