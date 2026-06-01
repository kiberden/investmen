<?php

declare(strict_types = 1);

$appEnv = strtolower(trim((string) env('APP_ENV', 'local')));
$defaultConnectionKey = $appEnv === 'production' ? 'prod' : 'sandbox';

return [
    'name' => 'T-Bank',
    'driver' => 'tbank',
    'default_environment' => $defaultConnectionKey,
    'default_connection_key' => $defaultConnectionKey,
    'connection_keys' => [
        'prod' => [
            'provider' => 'tbank',
            'base_url' => 'https://invest-public-api.tbank.ru/rest',
            'timeout' => 10,
        ],
        'sandbox' => [
            'provider' => 'tbank',
            'base_url' => 'https://sandbox-invest-public-api.tbank.ru/rest',
            'timeout' => 10,
        ],
    ],
];
