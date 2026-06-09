<?php

declare(strict_types = 1);

namespace App\Actions\Broker;

use App\Models\Broker;
use App\Services\BrokerGateway\PortfolioService;
use Illuminate\Support\Facades\Log;
use Throwable;

final class FetchBrokerPositionsAction
{
    public function __construct(
        private readonly PortfolioService $portfolioService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(Broker $broker, string $accountId): array
    {
        try {
            return $this->portfolioService->fetchPositions($broker, $accountId);
        } catch (Throwable $e) {
            Log::warning('FetchBrokerPositionsAction returned degraded response.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => (string) $broker->getAttribute('provider_code'),
                'operation' => 'getPositions',
                'error' => $e->getMessage(),
            ]);

            return [
                'broker_id' => (int) $broker->getKey(),
                'error' => $e->getMessage(),
            ];
        }
    }
}
