<?php

declare(strict_types = 1);

namespace Tests\Feature\Broker;

use App\Models\Broker;
use App\Models\BrokerCredential;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class BrokerCredentialSyncFromFormFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_broker_and_syncs_credential_from_form_fields(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = Request::create('/moonshine/brokers', 'POST', [
            'profile_name' => 'portfolio 1',
            'provider_code' => 'missing-provider',
            'token' => 'token-123',
            'expire_at' => '2026-07-01 10:00:00',
            'description' => 'Created from form',
        ]);
        $this->app->instance('request', $request);

        $broker = new Broker;
        $broker->setAttribute('profile_name', 'portfolio 1');
        $broker->setAttribute('provider_code', 'missing-provider');
        $broker->setAttribute('token', 'token-123');
        $broker->setAttribute('expire_at', '2026-07-01 10:00:00');
        $broker->setAttribute('description', 'Created from form');
        $broker->save();

        $this->assertDatabaseHas('brokers', [
            'id' => (int) $broker->getKey(),
            'profile_name' => 'portfolio 1',
            'provider_code' => 'missing-provider',
        ]);

        /** @var BrokerCredential $credential */
        $credential = BrokerCredential::query()->where('broker_id', (int) $broker->getKey())->firstOrFail();
        $this->assertSame('token-123', $credential->token);
        $this->assertSame('2026-07-01 10:00:00', $credential->expire_at?->format('Y-m-d H:i:s'));
    }

    public function test_it_updates_credential_without_sql_error_on_broker_update(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $broker = Broker::query()->create([
            'profile_name' => 'portfolio 2',
            'provider_code' => 'missing-provider',
            'description' => null,
        ]);

        BrokerCredential::query()->create([
            'broker_id' => (int) $broker->getKey(),
            'token' => 'old-token',
            'expire_at' => '2026-07-02 10:00:00',
        ]);

        $request = Request::create('/moonshine/brokers/'.$broker->getKey(), 'PUT', [
            'token' => 'new-token',
            'expire_at' => '2026-08-01 11:30:00',
        ]);
        $this->app->instance('request', $request);

        $broker->setAttribute('token', 'new-token');
        $broker->setAttribute('expire_at', '2026-08-01 11:30:00');
        $broker->setAttribute('description', 'Updated from form');
        $broker->save();

        $this->assertDatabaseHas('brokers', [
            'id' => (int) $broker->getKey(),
            'provider_code' => 'missing-provider',
            'description' => 'Updated from form',
        ]);

        /** @var BrokerCredential $credential */
        $credential = BrokerCredential::query()->where('broker_id', (int) $broker->getKey())->firstOrFail();
        $this->assertSame('new-token', $credential->token);
        $this->assertSame('2026-08-01 11:30:00', $credential->expire_at?->format('Y-m-d H:i:s'));
    }

    public function test_it_handles_empty_token_without_trying_to_write_virtual_columns(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = Request::create('/moonshine/brokers', 'POST', [
            'profile_name' => 'portfolio 3',
            'provider_code' => 'tbank',
            'token' => '',
            'description' => null,
        ]);
        $this->app->instance('request', $request);

        $broker = new Broker;
        $broker->setAttribute('profile_name', 'portfolio 3');
        $broker->setAttribute('provider_code', 'tbank');
        $broker->setAttribute('token', '');
        $broker->save();

        $this->assertDatabaseHas('brokers', [
            'id' => (int) $broker->getKey(),
            'profile_name' => 'portfolio 3',
            'provider_code' => 'tbank',
        ]);
        $this->assertDatabaseMissing('broker_credentials', [
            'broker_id' => (int) $broker->getKey(),
        ]);
    }
}
