<?php

declare(strict_types = 1);

namespace Tests\Feature\Broker;

use App\Models\Broker;
use App\Models\BrokerCredential;
use App\Models\User;
use App\Modules\BrokerGateway\Providers\TBank\Application\ListTBankInstrumentsAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ListTBankInstrumentsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_instruments_list_payload_for_broker(): void
    {
        $broker = $this->makeBrokerWithCredential();

        Http::fake([
            'https://example.test/rest/*' => Http::response(['instruments' => [['figi' => 'BBG000B9XRY4']]], 200),
        ]);

        $payload = app(ListTBankInstrumentsAction::class)->execute(
            broker: $broker,
            kind: 'share',
            request: [],
        );

        $this->assertSame('BBG000B9XRY4', data_get($payload, 'instruments.0.figi'));
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
