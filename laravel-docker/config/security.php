<?php

declare(strict_types=1);

return [
    'allowed_hosts' => array_values(array_filter(array_map(
        static fn (string $host): string => strtolower(trim($host)),
        explode(',', (string) env('APP_ALLOWED_HOSTS', '')),
    ))),

    'max_request_body_bytes' => 5 * 1024 * 1024,

    'session' => [
        'idle_timeout_seconds' => 30 * 60,
        'absolute_timeout_seconds' => 8 * 60 * 60,
    ],

    'rate_limit' => [
        'ip' => [
            'max_attempts' => 5,
            'interval_seconds' => 60,
        ],
        'account' => [
            'max_attempts' => 10,
            'interval_seconds' => 600,
        ],
    ],

    'argon2' => [
        'memory_cost' => 65_536,
        'time_cost' => 3,
        'threads' => 1,
    ],

    'auth' => [
        'generic_login_error_message' => 'Credenciales inválidas.',
    ],
];
