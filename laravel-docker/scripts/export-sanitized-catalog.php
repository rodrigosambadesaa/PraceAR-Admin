<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$catalog = [
    'version' => 1,
    'stalls' => DB::table('puestos')
        ->select(['id', 'caseta', 'nombre', 'id_nave', 'activo', 'tipo_unity', 'caseta_padre'])
        ->orderBy('id')
        ->get()
        ->map(static fn (object $row): array => (array) $row)
        ->all(),
    'translations' => DB::table('puestos_traducciones')
        ->select(['id', 'puesto_id', 'codigo_idioma', 'nombre_idioma', 'tipo', 'descripcion'])
        ->orderBy('id')
        ->get()
        ->map(static fn (object $row): array => (array) $row)
        ->all(),
];

$targetDirectory = __DIR__ . '/../database/data';
$target = $targetDirectory . '/catalog.json';

if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
    throw new RuntimeException('No se pudo crear database/data.');
}

$json = json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

if (file_put_contents($target, $json . PHP_EOL, LOCK_EX) === false) {
    throw new RuntimeException('No se pudo escribir el catálogo saneado.');
}

echo sprintf(
    "Catálogo saneado exportado: %d puestos, %d traducciones.\n",
    count($catalog['stalls']),
    count($catalog['translations']),
);
