<?php

declare(strict_types = 1);

namespace App\Console\Commands;

use App\Modules\BrokerGateway\Core\BrokerProviderRegistry;
use Illuminate\Console\Command;
use Throwable;

final class BrokerGatewayRunStreamCommand extends Command
{
    protected $signature = 'broker-gateway:stream {provider=tbank : Provider code, for example tbank}';

    protected $description = 'Run broker market data stream worker for configured provider.';

    public function __construct(
        private readonly BrokerProviderRegistry $providerRegistry,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $providerCode = (string) $this->argument('provider');

        try {
            $provider = $this->providerRegistry->forCode($providerCode);
            $provider->streamClient()->run();
            $this->components->warn('Stream client finished without live stream (MVP-1 fallback mode).');
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
