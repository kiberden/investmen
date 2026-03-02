<?php

declare(strict_types = 1);

namespace App\Services\BrokerGateway\Credentials;

use App\Models\Broker;
use App\Models\BrokerCredential;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

final class BrokerCredentialResolver
{
    public function resolveActive(Broker $broker): BrokerCredential
    {
        $brokerId = (int) $broker->getKey();

        /** @var BrokerCredential|null $credential */
        $credential = BrokerCredential::query()
            ->where('broker_id', $brokerId)
            ->where(static function (Builder $query): void {
                $query->whereNull('expire_at')->orWhere('expire_at', '>', now());
            })
            ->orderByDesc('id')
            ->first();

        if ($credential === null) {
            throw new RuntimeException(sprintf('Active credential not found for broker #%d.', $brokerId));
        }

        return $credential;
    }

    public function resolveForEnvironment(Broker $broker, string $environment): BrokerCredential
    {
        // The current schema stores a single credential per broker.
        // Environment is accepted to keep interface stable for future extension.
        unset($environment);

        return $this->resolveActive($broker);
    }

    public function decryptToken(BrokerCredential $credential): string
    {
        $token = $credential->getAttribute('token');

        if (!\is_string($token) || $token === '') {
            throw new RuntimeException(sprintf(
                'Token is empty for broker credential #%d.',
                (int) $credential->getKey(),
            ));
        }

        return $token;
    }

    public function decryptSecret(BrokerCredential $credential): string
    {
        $secret = $credential->getAttribute('secret');

        if (!\is_string($secret) || $secret === '') {
            throw new RuntimeException(sprintf(
                'Secret is empty for broker credential #%d.',
                (int) $credential->getKey(),
            ));
        }

        return $secret;
    }
}
