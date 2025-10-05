<?php

return [
    'max_request_body_bytes' => 16384,
    'php_limits' => [
        'post_max_size' => '16K',
        'upload_max_filesize' => '16K',
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
        'backoff' => [
            'enabled' => true,
            'start_after_failures' => 3,
            'base_seconds' => 5,
            'max_seconds' => 300,
            'streak_reset_seconds' => 900,
        ],
    ],
    'logging' => [
        'large_password_threshold' => 4096,
    ],
    'argon2' => [
        'memory_cost' => 65536, // 64 MB por derivación
        'time_cost' => 3,
        'threads' => 1,
    ],
];
