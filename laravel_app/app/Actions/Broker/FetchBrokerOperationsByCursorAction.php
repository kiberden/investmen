<?php

declare(strict_types = 1);

namespace App\Actions\Broker;

use App\Models\Broker;
use App\Services\BrokerGateway\PortfolioService;
use Illuminate\Support\Facades\Log;
use Throwable;

final class FetchBrokerOperationsByCursorAction
{
    public function __construct(
        private readonly PortfolioService $portfolioService,
    ) {}

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function execute(Broker $broker, array $request): array
    {
        try {
            return $this->portfolioService->fetchOperationsByCursor($broker, $request);
        } catch (Throwable $e) {
            Log::warning('FetchBrokerOperationsByCursorAction returned degraded response.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => (string) $broker->getAttribute('provider_code'),
                'operation' => 'getOperationsByCursor',
                'error' => $e->getMessage(),
            ]);

            return [
                'broker_id' => (int) $broker->getKey(),
                'error' => $e->getMessage(),
            ];
        }
    }
}
