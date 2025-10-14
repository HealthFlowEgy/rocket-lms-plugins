<?php

return [
    'name' => 'HealthPay',
    'version' => '1.0.0',
    'description' => 'HealthPay Payment Gateway Integration for Rocket LMS',
    'author' => 'HealthFlow',
    
    'settings' => [
        'api_endpoint' => [
            'type' => 'select',
            'options' => [
                'sandbox' => 'https://api.beta.healthpay.tech/graphql',
                'production' => 'https://api.healthpay.tech/graphql'
            ],
            'default' => 'sandbox',
            'label' => 'API Endpoint',
            'description' => 'Select the API endpoint (Sandbox for testing, Production for live)'
        ],
        'api_key' => [
            'type' => 'text',
            'required' => true,
            'label' => 'API Key',
            'description' => 'Your HealthPay API Key'
        ],
        'api_secret' => [
            'type' => 'password',
            'required' => true,
            'label' => 'API Secret',
            'description' => 'Your HealthPay API Secret'
        ],
        'mode' => [
            'type' => 'select',
            'options' => [
                'sandbox' => 'Sandbox',
                'live' => 'Live'
            ],
            'default' => 'sandbox',
            'label' => 'Mode',
            'description' => 'Operating mode'
        ],
        'enabled' => [
            'type' => 'boolean',
            'default' => false,
            'label' => 'Enable Gateway',
            'description' => 'Enable or disable HealthPay payment gateway'
        ],
        'webhook_secret' => [
            'type' => 'password',
            'required' => false,
            'label' => 'Webhook Secret',
            'description' => 'Secret key for webhook signature verification'
        ]
    ],
    
    'currencies' => ['EGP'],
    
    'logo' => '/plugins/HealthPay/assets/logo.png',
    
    'portal' => [
        'sandbox' => 'https://portal.beta.healthpay.tech',
        'production' => 'https://portal.healthpay.tech'
    ],
    
    'test_credentials' => [
        'username' => 'beta.account@healthpay.tech',
        'password' => 'BetaAcc@HealthPay2024'
    ]
];

