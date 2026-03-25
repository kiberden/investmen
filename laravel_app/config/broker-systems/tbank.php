<?php

declare(strict_types = 1);

return [
    'name' => 'T-Bank',
    'driver' => 'tbank',
    'default_environment' => 'sandbox',

    'environments' => [
        'prod' => [
            'base_url' => 'https://invest-public-api.tbank.ru/rest',
            'timeout' => 10,
            'app_name' => 'investman',
        ],

        'sandbox' => [
            'base_url' => 'https://sandbox-invest-public-api.tbank.ru/rest',
            'timeout' => 10,
            'app_name' => 'investman',
        ],
    ],
];
