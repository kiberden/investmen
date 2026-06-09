<?php

declare(strict_types = 1);

namespace Tests\Feature\Broker;

use App\Models\Broker;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AccountSyncConstraintsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unique_constraint_prevents_duplicate_external_account_per_broker(): void
    {
        $user = User::factory()->create();
        $broker = Broker::withoutEvents(static fn (): Broker => Broker::query()->forceCreate([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'Broker profile',
            'provider_code' => 'tbank',
            'description' => null,
        ]));

        DB::table('accounts')->insert([
            'broker_id' => (int) $broker->getKey(),
            'name' => 'Main',
            'external_account_id' => 'acc-1',
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::table('accounts')->insert([
            'broker_id' => (int) $broker->getKey(),
            'name' => 'Main duplicate',
            'external_account_id' => 'acc-1',
            'updated_at' => now(),
        ]);
    }

    public function test_update_or_create_is_idempotent_for_broker_and_external_account_id(): void
    {
        $user = User::factory()->create();
        $broker = Broker::withoutEvents(static fn (): Broker => Broker::query()->forceCreate([
            'user_id' => (int) $user->getKey(),
            'profile_name' => 'Broker profile',
            'provider_code' => 'tbank',
            'description' => null,
        ]));

        DB::table('accounts')->updateOrInsert(
            ['broker_id' => (int) $broker->getKey(), 'external_account_id' => 'acc-1'],
            ['name' => 'Main', 'updated_at' => now()],
        );

        DB::table('accounts')->updateOrInsert(
            ['broker_id' => (int) $broker->getKey(), 'external_account_id' => 'acc-1'],
            ['name' => 'Main updated', 'updated_at' => now()],
        );

        $this->assertSame(1, DB::table('accounts')
            ->where('broker_id', (int) $broker->getKey())
            ->where('external_account_id', 'acc-1')
            ->count());

        $this->assertDatabaseHas('accounts', [
            'broker_id' => (int) $broker->getKey(),
            'external_account_id' => 'acc-1',
            'name' => 'Main updated',
        ]);
    }
}
