<?php

declare(strict_types = 1);

namespace Tests\Feature\Redis;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;
use Throwable;

final class RedisConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'redis']);
    }

    public function test_redis_connection_is_available(): void
    {
        $pong = $this->pingRedis();

        $this->assertContains($pong, ['+PONG', 'PONG', true]);
    }

    public function test_redis_can_store_and_retrieve_values(): void
    {
        $this->pingRedis();

        $key = 'tests:redis:store-retrieve';
        $payload = ['provider' => 'tbank', 'account' => 'sandbox-account'];

        Cache::store('redis')->forget($key);
        Cache::store('redis')->put($key, $payload, 120);

        $this->assertSame($payload, Cache::store('redis')->get($key));
    }

    public function test_redis_ttl_is_working_correctly(): void
    {
        $this->pingRedis();

        $key = 'tests:redis:ttl';

        Cache::store('redis')->forget($key);
        Cache::store('redis')->put($key, 'ttl-value', 1);

        $this->assertSame('ttl-value', Cache::store('redis')->get($key));
        $startedAt = microtime(true);

        while (( microtime(true) - $startedAt ) < 3.0) {
            if (Cache::store('redis')->get($key) === null) {
                break;
            }

            usleep(200_000);
        }

        $this->assertNull(Cache::store('redis')->get($key));
    }

    private function pingRedis(): mixed
    {
        try {
            return Redis::connection('default')->ping();
        } catch (Throwable $e) {
            $this->markTestSkipped(sprintf('Redis is not available: %s', $e->getMessage()));

            throw $e;
        }
    }
}
