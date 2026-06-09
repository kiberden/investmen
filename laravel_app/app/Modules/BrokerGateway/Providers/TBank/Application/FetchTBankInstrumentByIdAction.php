<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Application;

use App\Models\Broker;
use App\Modules\BrokerGateway\Providers\TBank\Application\Instruments\InstrumentsGateway;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Adapters\LegacyTBankProviderConfigAdapter;
use App\Services\BrokerGateway\Credentials\BrokerCredentialResolver;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class FetchTBankInstrumentByIdAction
{
    public function __construct(
        private readonly LegacyTBankProviderConfigAdapter $legacyProviderConfigAdapter,
        private readonly BrokerCredentialResolver $credentialResolver,
        private readonly InstrumentsGateway $instrumentsGateway,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(Broker $broker, string $kind, string $idType, string $id): array
    {
        $normalizedId = trim($id);

        if ($normalizedId === '') {
            Log::warning('FetchTBankInstrumentByIdAction got empty instrument id.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => (string) $broker->getAttribute('provider_code'),
                'kind' => $kind,
                'operation' => 'getInstrumentById',
            ]);

            throw new RuntimeException('Instrument id is required.');
        }

        try {
            $providerConfig = $this->legacyProviderConfigAdapter->resolveForBroker($broker);
            $credential = $this->credentialResolver->resolveForEnvironment($broker, $providerConfig['environment']);
            $token = $this->credentialResolver->decryptToken($credential);

            return $this->instrumentsGateway->getInstrumentById(
                kind: $kind,
                idType: $idType,
                id: $normalizedId,
                context: [
                    'base_url' => $providerConfig['base_url'],
                    'token' => $token,
                    'timeout' => $providerConfig['timeout'],
                    'app_name' => $providerConfig['app_name'],
                ],
            );
        } catch (Throwable $e) {
            Log::error('FetchTBankInstrumentByIdAction failed.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => (string) $broker->getAttribute('provider_code'),
                'kind' => $kind,
                'operation' => 'getInstrumentById',
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Failed to fetch T-Bank instrument by id.', previous: $e);
        }
    }
}
