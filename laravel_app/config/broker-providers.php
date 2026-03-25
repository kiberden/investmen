<?php

declare(strict_types = 1);

$brokerSystems = require __DIR__.'/broker-systems.php';

/** @var mixed $allSystems */
$allSystems = $brokerSystems['systems'] ?? [];
/** @var mixed $enabledSystems */
$enabledSystems = $brokerSystems['enabled'] ?? [];

$providers = [];

if (\is_array($allSystems) && \is_array($enabledSystems)) {
    foreach ($enabledSystems as $providerCode) {
        $normalizedProviderCode = \is_string($providerCode) ? strtolower(trim($providerCode)) : '';
        if ($normalizedProviderCode === '') {
            continue;
        }

        $providerConfig = $allSystems[$normalizedProviderCode] ?? null;
        if (\is_array($providerConfig)) {
            $providers[$normalizedProviderCode] = $providerConfig;
        }
    }
}

return [
    'default_environment' => env('BROKER_GATEWAY_DEFAULT_ENV', default: 'sandbox'),
    'cache_ttl_seconds' => (int) env(
        'BROKER_GATEWAY_CACHE_TTL_SECONDS',
        default: (int) env('CACHE_TTL_SECONDS', default: 120),
    ),
    'providers' => $providers,
];
