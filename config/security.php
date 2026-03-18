<?php

declare(strict_types=1);

return [
    "max_request_body_bytes" => 16384,
    "php_limits" => [
        "post_max_size" => "16K",
        "upload_max_filesize" => "16K",
    ],
    "rate_limit" => [
        "ip" => [
            "max_attempts" => 5,
            "interval_seconds" => 60,
        ],
        "account" => [
            "max_attempts" => 10,
            "interval_seconds" => 600,
        ],
        "backoff" => [
            "enabled" => true,
            "start_after_failures" => 3,
            "base_seconds" => 5,
            "max_seconds" => 300,
            "streak_reset_seconds" => 900,
        ],
    ],
    "logging" => [
        "large_password_threshold" => 4096,
    ],
    "argon2" => [
        "memory_cost" => 65536, // 64 MB por derivación
        "time_cost" => 3,
        "threads" => 1,
    ],
    "auth" => [
        // Evita enumeración de usuarios devolviendo siempre el mismo mensaje de error.
        "generic_login_error_message" => "Credenciales inválidas.",
    ],
    "headers" => [
        "enabled" => true,
        "referrer_policy" => "strict-origin-when-cross-origin",
        "permissions_policy" =>
            "geolocation=(), microphone=(), camera=(), payment=(), usb=()",
        "content_security_policy" => [
            "default-src" => ["'self'"],
            "base-uri" => ["'self'"],
            "frame-ancestors" => ["'self'"],
            "form-action" => ["'self'"],
            "script-src" => [
                "'self'",
                "'unsafe-inline'",
                "https://cdn.jsdelivr.net",
            ],
            "style-src" => [
                "'self'",
                "'unsafe-inline'",
                "https://cdn.jsdelivr.net",
                "https://fonts.googleapis.com",
            ],
            "font-src" => ["'self'", "https://fonts.gstatic.com", "data:"],
            "img-src" => ["'self'", "data:", "https:"],
            "connect-src" => [
                "'self'",
                "https://www.virustotal.com",
                "https://api.pwnedpasswords.com",
            ],
            "object-src" => ["'none'"],
            "upgrade-insecure-requests" => [],
        ],
    ],
];
