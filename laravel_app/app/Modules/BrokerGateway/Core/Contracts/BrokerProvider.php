<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Core\Contracts;

use App\Models\Broker;
use App\Modules\BrokerGateway\Core\DTO\SyncBrokerAccountResult;

/**
 * Контракт провайдера для брокера.
 */
interface BrokerProvider
{
    public function code(): string;

    public function syncAccount(Broker $broker): SyncBrokerAccountResult;

    public function streamClient(): BrokerStreamClient;
}
