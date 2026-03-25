<?php

declare(strict_types = 1);

namespace Tests\Fixtures\Services;

final readonly class ExampleService
{
    public function __construct(
        private string $provider = 'tbank',
        private string $environment = 'sandbox',
    ) {}

    public function descriptor(): string
    {
        return sprintf('%s:%s', $this->provider, $this->environment);
    }
}
