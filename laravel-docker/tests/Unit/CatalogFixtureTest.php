<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class CatalogFixtureTest extends TestCase
{
    public function test_catalog_fixture_contains_only_public_allowlisted_columns(): void
    {
        $catalog = json_decode(
            (string) file_get_contents(__DIR__ . '/../../database/data/catalog.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->assertSame(1, $catalog['version']);
        $this->assertNotEmpty($catalog['stalls']);
        $this->assertNotEmpty($catalog['translations']);

        $stallKeys = ['id', 'caseta', 'nombre', 'id_nave', 'activo', 'tipo_unity', 'caseta_padre'];
        $translationKeys = ['id', 'puesto_id', 'codigo_idioma', 'nombre_idioma', 'tipo', 'descripcion'];

        foreach ($catalog['stalls'] as $stall) {
            $this->assertSame([], array_diff(array_keys($stall), $stallKeys));
            $this->assertArrayNotHasKey('contacto', $stall);
            $this->assertArrayNotHasKey('telefono', $stall);
            $this->assertArrayNotHasKey('imagen', $stall);
        }

        foreach ($catalog['translations'] as $translation) {
            $this->assertSame([], array_diff(array_keys($translation), $translationKeys));
        }
    }
}
