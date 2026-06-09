<?php

declare(strict_types = 1);

namespace App\Console\Commands;

use App\Models\Broker;
use App\Modules\BrokerGateway\Providers\TBank\Application\SyncTBankBrokerAccountsAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

final class BackfillTBankExternalAccountIdsCommand extends Command
{
    protected $signature = 'broker:tbank-backfill-external-account-ids {--broker-id=* : Limit backfill to selected broker IDs}';

    protected $description = 'Backfill external_account_id values for TBank brokers and create missing multi-account rows.';

    public function __construct(
        private readonly SyncTBankBrokerAccountsAction $syncTBankBrokerAccountsAction,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        /** @var array<int, int|string> $brokerIds */
        $brokerIds = (array) $this->option('broker-id');

        $query = Broker::query()
            ->where('provider_code', 'tbank')
            ->whereHas('credential');

        if ($brokerIds !== []) {
            $query->whereIn('id', $brokerIds);
        }

        $brokers = $query->orderBy('id')->get();

        if ($brokers->isEmpty()) {
            $this->components->warn('No eligible TBank brokers found for backfill.');
            return self::SUCCESS;
        }

        $processed = 0;
        $failed = 0;

        foreach ($brokers as $broker) {
            try {
                $this->syncTBankBrokerAccountsAction->execute($broker);
                $processed++;
            } catch (Throwable $e) {
                $failed++;
                Log::warning('BackfillTBankExternalAccountIdsCommand failed for broker.', [
                    'broker_id' => (int) $broker->getKey(),
                    'provider_code' => (string) $broker->getAttribute('provider_code'),
                    'error' => $e->getMessage(),
                ]);
                $this->components->warn(sprintf(
                    'Backfill failed for broker #%d: %s',
                    (int) $broker->getKey(),
                    $e->getMessage(),
                ));
            }
        }

        $this->components->info(sprintf(
            'Backfill complete. Processed: %d, failed: %d.',
            $processed,
            $failed,
        ));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
