<?php

declare(strict_types = 1);

$systems = [
    'tbank' => require __DIR__.'/broker-systems/tbank.php',
];

$enabled = array_map(
    static fn(string $code): string => strtolower(trim($code)),
    array_filter(
        explode(',', (string) env('BROKER_SYSTEMS_ENABLED', 'tbank')),
        static fn(string $code): bool => trim($code) !== '',
    ),
);

if ($enabled === []) {
    $enabled = array_keys($systems);
}

return [
    'enabled' => array_values(array_unique($enabled)),
    'systems' => $systems,
];
