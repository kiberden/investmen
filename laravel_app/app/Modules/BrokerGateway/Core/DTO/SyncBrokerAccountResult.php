<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Core\DTO;

final class SyncBrokerAccountResult
{
    public function __construct(
        private readonly string $accountName,
    ) {}

    public function accountName(): string
    {
        return $this->accountName;
    }
}
