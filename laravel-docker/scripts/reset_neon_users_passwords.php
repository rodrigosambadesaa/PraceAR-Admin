<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = Illuminate\Support\Facades\DB::table('usuarios')
    ->select(['id', 'login'])
    ->orderBy('id')
    ->get();

if ($users->isEmpty()) {
    fwrite(STDOUT, "No hay usuarios en la tabla usuarios." . PHP_EOL);
    exit(0);
}

$updated = [];

foreach ($users as $user) {
    $id = (int) $user->id;
    $login = (string) $user->login;

    $plainPassword = sprintf('PraceAR_%s_%d!2026', preg_replace('/[^A-Za-z0-9]/', '', $login), $id);
    $hashedPassword = App\Support\PracearSupport::hashPassword($plainPassword);

    Illuminate\Support\Facades\DB::table('usuarios')
        ->where('id', $id)
        ->update(['password' => $hashedPassword]);

    $updated[] = [
        'id' => $id,
        'login' => $login,
        'plain_password' => $plainPassword,
        'password_hash' => $hashedPassword,
    ];
}

fwrite(STDOUT, json_encode(['updated' => $updated], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL);
