<?php

return [
    'currency' => 'jpy',
    'grace_days' => 7,

    'plans' => [
        'free' => [
            'name' => '無料プラン',
            'monthly_price' => 0,
            'yearly_price' => 0,
            'limits' => [
                'original_characters' => 30,
                'relationships' => 100,
                'prompts' => 50,
                'stories' => 10,
            ],
        ],
        'plus' => [
            'name' => 'Oshi-Wiki Plus',
            'monthly_price' => 480,
            'yearly_price' => 4800,
            'limits' => [
                'original_characters' => 150,
                'relationships' => 1000,
                'prompts' => 500,
                'stories' => 200,
            ],
        ],
    ],

    'legal' => [
        'operator_name' => env('LEGAL_OPERATOR_NAME', 'Oshi-Wiki運営'),
        'contact_email' => env('LEGAL_CONTACT_EMAIL', ''),
        'address_disclosure' => env(
            'LEGAL_ADDRESS_DISCLOSURE',
            '請求があった場合、遅滞なく開示します。'
        ),
        'phone_disclosure' => env(
            'LEGAL_PHONE_DISCLOSURE',
            '請求があった場合、遅滞なく開示します。'
        ),
    ],
];
