<?php

declare(strict_types = 1);

namespace Tests\Feature\Broker;

use App\Models\Broker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BrokerProviderCodePersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_normalized_provider_code_on_create(): void
    {
        $user = User::factory()->create();

        $broker = Broker::withoutEvents(static fn(): Broker => Broker::query()->forceCreate([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'Provider create',
            'provider_code' => ' TBANK ',
            'description' => null,
        ]));

        $this->assertSame('tbank', $broker->provider_code);
        $this->assertDatabaseHas('brokers', [
            'id' => (int) $broker->getKey(),
            'provider_code' => 'tbank',
        ]);
    }

    public function test_it_persists_provider_code_on_update(): void
    {
        $user = User::factory()->create();

        $broker = Broker::withoutEvents(static fn(): Broker => Broker::query()->forceCreate([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'Provider update',
            'provider_code' => 'tbank',
            'description' => null,
        ]));

        Broker::withoutEvents(static function () use ($broker): void {
            $broker->forceFill(['provider_code' => 'alpha'])->save();
        });

        $this->assertDatabaseHas('brokers', [
            'id' => (int) $broker->getKey(),
            'provider_code' => 'alpha',
        ]);
    }
}
