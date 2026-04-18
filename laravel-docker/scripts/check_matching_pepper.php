<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$hash = '$argon2id$v=19$m=65536,t=3,p=1$cGR5ekIvaFlGV1VVZkxNcg$hBGbbx5/7ZdbqHPva4CEVXHCzKSJFtHhIaCHN8Sd4hI';
$matched = App\Support\PracearSupport::matchingPepper('Admin1234!!Aa', $hash);

var_export($matched);
echo PHP_EOL;
