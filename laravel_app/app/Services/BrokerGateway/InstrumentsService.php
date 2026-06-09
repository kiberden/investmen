<?php

declare(strict_types = 1);

namespace App\Services\BrokerGateway;

use App\Models\Broker;
use App\Modules\BrokerGateway\Providers\TBank\Application\FetchTBankInstrumentByIdAction;
use App\Modules\BrokerGateway\Providers\TBank\Application\ListTBankInstrumentsAction;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class InstrumentsService
{
    public function __construct(
        private readonly FetchTBankInstrumentByIdAction $fetchTBankInstrumentByIdAction,
        private readonly ListTBankInstrumentsAction $listTBankInstrumentsAction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function fetchInstrumentById(Broker $broker, string $kind, string $idType, string $id): array
    {
        try {
            return $this->fetchTBankInstrumentByIdAction->execute($broker, $kind, $idType, $id);
        } catch (Throwable $e) {
            Log::error('InstrumentsService failed to fetch instrument by id.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => (string) $broker->getAttribute('provider_code'),
                'kind' => $kind,
                'operation' => 'getInstrumentById',
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Failed to fetch broker instrument by id.', previous: $e);
        }
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function listInstruments(Broker $broker, string $kind, array $request): array
    {
        try {
            return $this->listTBankInstrumentsAction->execute($broker, $kind, $request);
        } catch (Throwable $e) {
            Log::error('InstrumentsService failed to list instruments.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => (string) $broker->getAttribute('provider_code'),
                'kind' => $kind,
                'operation' => 'listInstruments',
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Failed to list broker instruments.', previous: $e);
        }
    }
}
