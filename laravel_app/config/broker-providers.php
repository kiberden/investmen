<?php

declare(strict_types = 1);

$brokerSystems = require __DIR__.'/broker-systems.php';

/** @var mixed $allSystems */
$allSystems = $brokerSystems['systems'] ?? [];
/** @var mixed $enabledSystems */
$enabledSystems = $brokerSystems['enabled'] ?? [];

$providers = [];
$connectionKeys = [];
$defaultEnvironment = 'sandbox';

if (\is_array($allSystems) && \is_array($enabledSystems)) {
    foreach ($enabledSystems as $providerCode) {
        $normalizedProviderCode = \is_string($providerCode) ? strtolower(trim($providerCode)) : '';
        if ($normalizedProviderCode === '') {
            continue;
        }

        $providerConfig = $allSystems[$normalizedProviderCode] ?? null;
        if (\is_array($providerConfig)) {
            $providers[$normalizedProviderCode] = $providerConfig;

            /** @var mixed $providerConnectionKeys */
            $providerConnectionKeys = $providerConfig['connection_keys'] ?? [];
            if (\is_array($providerConnectionKeys)) {
                $defaultConnectionKey = \is_string($providerConfig['default_connection_key'] ?? null)
                    ? strtolower(trim((string) $providerConfig['default_connection_key']))
                    : '';

                foreach ($providerConnectionKeys as $connectionKey => $connectionConfig) {
                    $normalizedConnectionKey = \is_string($connectionKey) ? strtolower(trim($connectionKey)) : '';
                    if ($normalizedConnectionKey === '' || !\is_array($connectionConfig)) {
                        continue;
                    }

                    $connectionKeys[$normalizedConnectionKey] = [
                        ...$connectionConfig,
                        'provider' => (string) ($connectionConfig['provider'] ?? $normalizedProviderCode),
                    ];
                }

                if ($defaultConnectionKey !== '' && isset($connectionKeys[$defaultConnectionKey])) {
                    $defaultEnvironment = $defaultConnectionKey;
                }
            }
        }
    }
}

return [
    'default_environment' => $defaultEnvironment,
    'cache_ttl_seconds' => (int) env(
        'BROKER_GATEWAY_CACHE_TTL_SECONDS',
        default: (int) env('CACHE_TTL_SECONDS', default: 120),
    ),
    'providers' => $providers,
    // Flat lookup table for UI select/options and future key-based resolver.
    'connection_keys' => $connectionKeys,
];
