<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Password pepper rotation
|--------------------------------------------------------------------------
|
| Secrets never belong in this file. PASSWORD_PEPPER_CURRENT is the only
| pepper used for new hashes. Previous peppers may be supplied temporarily
| as a JSON array while existing passwords are transparently rehashed.
|
*/

return [
    'current' => env('PASSWORD_PEPPER_CURRENT'),
    'legacy' => env('PASSWORD_PEPPER_LEGACY_JSON', '[]'),
];
