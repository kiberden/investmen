<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Application;

use App\Models\Broker;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Adapters\LegacyTBankProviderConfigAdapter;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankOperationsRestClient;
use App\Services\BrokerGateway\Credentials\BrokerCredentialResolver;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class FetchTBankPortfolioAction
{
    public function __construct(
        private readonly LegacyTBankProviderConfigAdapter $legacyProviderConfigAdapter,
        private readonly BrokerCredentialResolver $credentialResolver,
        private readonly TBankOperationsRestClient $operationsRestClient,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(Broker $broker, string $accountId): array
    {
        $normalizedAccountId = trim($accountId);

        if ($normalizedAccountId === '') {
            Log::warning('FetchTBankPortfolioAction got empty accountId.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => (string) $broker->getAttribute('provider_code'),
                'operation' => 'getPortfolio',
            ]);

            throw new RuntimeException('T-Bank accountId is required for portfolio request.');
        }

        try {
            $providerConfig = $this->legacyProviderConfigAdapter->resolveForBroker($broker);
            $credential = $this->credentialResolver->resolveForEnvironment($broker, $providerConfig['environment']);
            $token = $this->credentialResolver->decryptToken($credential);

            return $this->operationsRestClient->getPortfolio(
                baseUrl: $providerConfig['base_url'],
                token: $token,
                timeout: $providerConfig['timeout'],
                appName: $providerConfig['app_name'],
                accountId: $normalizedAccountId,
            );
        } catch (Throwable $e) {
            Log::error('FetchTBankPortfolioAction failed.', [
                'broker_id' => (int) $broker->getKey(),
                'provider_code' => (string) $broker->getAttribute('provider_code'),
                'operation' => 'getPortfolio',
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Failed to fetch T-Bank portfolio.', previous: $e);
        }
    }
}
