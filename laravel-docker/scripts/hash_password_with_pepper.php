<?php

declare(strict_types=1);

if ($argc < 2) {
    fwrite(STDERR, "Uso: php scripts/hash_password_with_pepper.php <password>" . PHP_EOL);
    exit(1);
}

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$plainPassword = (string) $argv[1];
$hash = App\Support\PracearSupport::hashPassword($plainPassword);

echo $hash . PHP_EOL;
