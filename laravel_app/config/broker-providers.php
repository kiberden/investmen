<?php

declare(strict_types = 1);

return [
    'default_environment' => env('BROKER_GATEWAY_DEFAULT_ENV', default: 'sandbox'),

    'providers' => [
        'tbank' => [
            'name' => 'T-Bank',
            'driver' => 'tbank',
            'default_environment' => env('TBANK_DEFAULT_ENV', default: 'sandbox'),

            'environments' => [
                'prod' => [
                    'base_url' => env('TBANK_PROD_BASE_URL', default: 'https://invest-public-api.tbank.ru/rest'),
                    'timeout' => (int) env('TBANK_PROD_TIMEOUT', default: 10),
                    'app_name' => env('TBANK_APP_NAME', default: 'investman'),
                ],

                'sandbox' => [
                    'base_url' => env(
                        'TBANK_SANDBOX_BASE_URL',
                        default: 'https://sandbox-invest-public-api.tbank.ru/rest',
                    ),
                    'timeout' => (int) env('TBANK_SANDBOX_TIMEOUT', default: 10),
                    'app_name' => env('TBANK_APP_NAME', default: 'investman'),
                ],
            ],
        ],
    ],
];
