<?php

declare(strict_types = 1);

namespace App\Services\BrokerGateway;

use App\Models\Broker;
use App\Modules\BrokerGateway\Providers\TBank\Application\FetchTBankOperationsByCursorAction;
use App\Modules\BrokerGateway\Providers\TBank\Application\FetchTBankPortfolioAction;
use App\Modules\BrokerGateway\Providers\TBank\Application\FetchTBankPositionsAction;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class PortfolioService
{
    public function __construct(
        private readonly FetchTBankPortfolioAction $fetchTBankPortfolioAction,
        private readonly FetchTBankPositionsAction $fetchTBankPositionsAction,
        private readonly FetchTBankOperationsByCursorAction $fetchTBankOperationsByCursorAction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function fetchPortfolio(Broker $broker, string $accountId): array
    {
        try {
            return $this->fetchTBankPortfolioAction->execute($broker, $accountId);
        } catch (Throwable $e) {
            Log::error('PortfolioService failed to fetch portfolio.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => (string) $broker->getAttribute('provider_code'),
                'operation' => 'getPortfolio',
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Failed to fetch broker portfolio.', previous: $e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchPositions(Broker $broker, string $accountId): array
    {
        try {
            return $this->fetchTBankPositionsAction->execute($broker, $accountId);
        } catch (Throwable $e) {
            Log::error('PortfolioService failed to fetch positions.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => (string) $broker->getAttribute('provider_code'),
                'operation' => 'getPositions',
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Failed to fetch broker positions.', previous: $e);
        }
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function fetchOperationsByCursor(Broker $broker, array $request): array
    {
        try {
            return $this->fetchTBankOperationsByCursorAction->execute($broker, $request);
        } catch (Throwable $e) {
            Log::error('PortfolioService failed to fetch operations by cursor.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => (string) $broker->getAttribute('provider_code'),
                'operation' => 'getOperationsByCursor',
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Failed to fetch broker operations by cursor.', previous: $e);
        }
    }
}
