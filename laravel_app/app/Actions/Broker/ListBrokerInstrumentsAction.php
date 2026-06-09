<?php

declare(strict_types = 1);

namespace App\Actions\Broker;

use App\Models\Broker;
use App\Services\BrokerGateway\InstrumentsService;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ListBrokerInstrumentsAction
{
    public function __construct(
        private readonly InstrumentsService $instrumentsService,
    ) {}

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function execute(Broker $broker, string $kind, array $request): array
    {
        try {
            return $this->instrumentsService->listInstruments($broker, $kind, $request);
        } catch (Throwable $e) {
            Log::warning('ListBrokerInstrumentsAction returned degraded response.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => (string) $broker->getAttribute('provider_code'),
                'kind' => $kind,
                'operation' => 'listInstruments',
                'error' => $e->getMessage(),
            ]);

            return [
                'broker_id' => (int) $broker->getKey(),
                'error' => $e->getMessage(),
            ];
        }
    }
}
