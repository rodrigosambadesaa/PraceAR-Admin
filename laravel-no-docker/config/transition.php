<?php

return [
    'legacy_base_url' => env('LEGACY_BASE_URL', 'http://127.0.0.1'),
    'legacy_paths' => [
        'home' => env('LEGACY_HOME_PATH', 'index.php'),
        'login' => env('LEGACY_LOGIN_PATH', 'login.php'),
        'admin' => env('LEGACY_ADMIN_PATH', 'admin/index.php'),
    ],
];
