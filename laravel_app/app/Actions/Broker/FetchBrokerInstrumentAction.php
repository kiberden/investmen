<?php

declare(strict_types = 1);

namespace App\Actions\Broker;

use App\Models\Broker;
use App\Services\BrokerGateway\InstrumentsService;
use Illuminate\Support\Facades\Log;
use Throwable;

final class FetchBrokerInstrumentAction
{
    public function __construct(
        private readonly InstrumentsService $instrumentsService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(Broker $broker, string $kind, string $idType, string $id): array
    {
        try {
            return $this->instrumentsService->fetchInstrumentById($broker, $kind, $idType, $id);
        } catch (Throwable $e) {
            Log::warning('FetchBrokerInstrumentAction returned degraded response.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => (string) $broker->getAttribute('provider_code'),
                'kind' => $kind,
                'operation' => 'getInstrumentById',
                'error' => $e->getMessage(),
            ]);

            return [
                'broker_id' => (int) $broker->getKey(),
                'error' => $e->getMessage(),
            ];
        }
    }
}
