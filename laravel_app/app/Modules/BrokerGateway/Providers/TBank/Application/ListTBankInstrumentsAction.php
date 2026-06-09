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

final class ListTBankInstrumentsAction
{
    public function __construct(
        private readonly LegacyTBankProviderConfigAdapter $legacyProviderConfigAdapter,
        private readonly BrokerCredentialResolver $credentialResolver,
        private readonly InstrumentsGateway $instrumentsGateway,
    ) {}

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function execute(Broker $broker, string $kind, array $request): array
    {
        try {
            $providerConfig = $this->legacyProviderConfigAdapter->resolveForBroker($broker);
            $credential = $this->credentialResolver->resolveForEnvironment($broker, $providerConfig['environment']);
            $token = $this->credentialResolver->decryptToken($credential);

            return $this->instrumentsGateway->listInstruments(
                kind: $kind,
                request: $request,
                context: [
                    'base_url' => $providerConfig['base_url'],
                    'token' => $token,
                    'timeout' => $providerConfig['timeout'],
                    'app_name' => $providerConfig['app_name'],
                ],
            );
        } catch (Throwable $e) {
            Log::error('ListTBankInstrumentsAction failed.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => (string) $broker->getAttribute('provider_code'),
                'kind' => $kind,
                'operation' => 'listInstruments',
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Failed to list T-Bank instruments.', previous: $e);
        }
    }
}
