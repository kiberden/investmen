<?php

declare(strict_types = 1);

namespace Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

abstract class ServiceTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'array']);
    }

    /**
     * @param callable(Request):bool|null $matcher
     */
    protected function fakeHttpResponses(array $responses, ?callable $matcher = null): void
    {
        if ($matcher === null) {
            Http::fake($responses);

            return;
        }

        Http::fake([
            $matcher => Http::response($responses, 200),
        ]);
    }

    protected function flushArrayCache(): void
    {
        Cache::store('array')->flush();
    }
}
