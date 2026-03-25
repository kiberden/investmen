<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Core\Contracts;

/**
 * Контракт потокового обмена брокера.
 */
interface BrokerStreamClient
{
    public function run(): void;
}
