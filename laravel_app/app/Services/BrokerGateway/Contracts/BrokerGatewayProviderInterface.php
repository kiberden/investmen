<?php

declare(strict_types = 1);

namespace App\Services\BrokerGateway\Contracts;

interface BrokerGatewayProviderInterface
{
    public function getCode(): string;

    public function getName(): string;

    public function getEnvironment(): string;

    public function getBaseUrl(): string;

    public function getTimeout(): int;

    public function getAppName(): string;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
