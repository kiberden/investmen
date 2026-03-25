<?php

declare(strict_types = 1);

namespace Tests\Unit\Services;

use Tests\Fixtures\Services\ExampleService;
use Tests\ServiceTestCase;

final class ExampleServiceTest extends ServiceTestCase
{
    public function test_example_service_can_be_instantiated(): void
    {
        $service = new ExampleService;

        $this->assertInstanceOf(ExampleService::class, $service);
    }

    public function test_example_service_returns_expected_value(): void
    {
        $service = new ExampleService(provider: 'tbank', environment: 'sandbox');

        $this->assertSame('tbank:sandbox', $service->descriptor());
    }
}
