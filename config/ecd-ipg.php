<?php

return [
    'default_timeout' => env('DEFAULT_PAYMENT_TIMEOUT', 30),

    'default_gateway' => env('DEFAULT_PAYMENT_GATEWAY', 'ecd'),

    'default_currency' => env('DEFAULT_CURRENCY', 'IRR'),

    'after_payment' => [
        'success_redirect_url' => '/invoice',
        'failed_redirect_url' => '/',
        'transaction_uuid_param_name' => 'transaction',
        'payable_id_param_name' => 'invoice',
    ],

    'gateways' => [
        'ecd' => [
            'name' => 'ecd',
            'class' => Bahramn\EcdIpg\Gateways\Ecd\EcdGateway::class,
            'active' => env('ECD_GATEWAY_ACTIVE', false),
            'terminal_id' => env('ECD_TERMINAL_ID'),
            'key' => env('ECD_GATEWAY_KEY'),
            'callback_url' => '/payment/gateways/ecd/callback',
            'locale' => 'fa',
        ],
        'test' => [
            'name' => 'test',
            'class' => Bahramn\EcdIpg\Gateways\Test\TestGateway::class,
            'active' => env('TEST_GATEWAY_ACTIVE', false),
            'callback_url' => '/payment/gateways/test/callback',
        ],
    ],
];
