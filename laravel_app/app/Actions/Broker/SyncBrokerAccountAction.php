<?php

declare(strict_types = 1);

namespace App\Actions\Broker;

use App\Models\Account;
use App\Models\Broker;
use App\Modules\BrokerGateway\Core\BrokerProviderCodeResolver;
use App\Modules\BrokerGateway\Core\BrokerProviderRegistry;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncBrokerAccountAction
{
    public function __construct(
        private readonly BrokerProviderRegistry $providerRegistry,
        private readonly BrokerProviderCodeResolver $providerCodeResolver,
    ) {}

    public function execute(Broker $broker): void
    {
        $providerCode = $this->providerCodeResolver->resolveForBroker($broker);

        try {
            $provider = $this->providerRegistry->forCode($providerCode);
            $result = $provider->syncAccount($broker);

            Account::query()->updateOrCreate(['broker_id' =>
                (int) $broker->getKey()], ['name' => $result->accountName()]);
        } catch (Throwable $e) {
            Log::warning('Failed to sync broker account via broker provider module.', [
                'broker_id' => (int) $broker->getKey(),
                'provider' => $providerCode,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
