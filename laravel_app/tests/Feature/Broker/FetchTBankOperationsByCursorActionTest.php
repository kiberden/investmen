<?php

declare(strict_types = 1);

namespace Tests\Feature\Broker;

use App\Models\Broker;
use App\Models\BrokerCredential;
use App\Models\User;
use App\Modules\BrokerGateway\Providers\TBank\Application\FetchTBankOperationsByCursorAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class FetchTBankOperationsByCursorActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_operations_by_cursor_payload_for_broker(): void
    {
        $broker = $this->makeBrokerWithCredential();

        Http::fake([
            'https://example.test/rest/*' => Http::response(['items' => [['id' => 'op-1']], 'hasNext' => false], 200),
        ]);

        $payload = app(FetchTBankOperationsByCursorAction::class)->execute($broker, [
            'accountId' => 'acc-1',
            'cursor' => 'cursor-1',
            'limit' => 10,
        ]);

        $this->assertSame('op-1', data_get($payload, 'items.0.id'));
    }

    private function makeBrokerWithCredential(): Broker
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
        $broker = Broker::withoutEvents(static fn (): Broker => Broker::query()->forceCreate([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'Broker',
            'provider_code' => 'tbank',
            'description' => null,
        ]));

        BrokerCredential::query()->create([
            'broker_id' => (int) $broker->getKey(),
            'token' => hash('sha256', (string) microtime(true)),
            'expire_at' => null,
        ]);

        return $broker;
    }
}
