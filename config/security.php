<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blocked IP addresses
    |--------------------------------------------------------------------------
    |
    | SECURITY_BLOCKED_IPSへカンマ区切りで指定します。
    | 単一IPとCIDRの両方を利用できます。
    |
    | 例:
    | SECURITY_BLOCKED_IPS=203.0.113.10,198.51.100.0/24
    |
    */
    'blocked_ips' => array_values(array_filter(array_map(
        static fn (string $value): string => trim($value),
        explode(',', (string) env('SECURITY_BLOCKED_IPS', ''))
    ))),

    'login' => [
        'account_max_attempts' => (int) env(
            'SECURITY_LOGIN_ACCOUNT_MAX_ATTEMPTS',
            5
        ),
        'ip_max_attempts' => (int) env(
            'SECURITY_LOGIN_IP_MAX_ATTEMPTS',
            20
        ),
        'decay_seconds' => (int) env(
            'SECURITY_LOGIN_DECAY_SECONDS',
            900
        ),
    ],
];
