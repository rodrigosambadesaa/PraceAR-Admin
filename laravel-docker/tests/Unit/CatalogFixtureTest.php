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

        $this->assertSame(2, $catalog['version']);
        $this->assertNotEmpty($catalog['stalls']);
        $this->assertNotEmpty($catalog['translations']);

        $stallIds = array_column($catalog['stalls'], 'id');
        $translationIds = array_column($catalog['translations'], 'id');
        $stallCodes = array_column($catalog['stalls'], 'caseta');

        $this->assertSame(range(1, count($stallIds)), $stallIds);
        $this->assertSame(range(1, count($translationIds)), $translationIds);
        $this->assertCount(count($stallCodes), array_unique($stallCodes));

        $stallKeys = ['id', 'caseta', 'nombre', 'id_nave', 'activo', 'tipo_unity', 'caseta_padre'];
        $translationKeys = ['id', 'puesto_id', 'codigo_idioma', 'nombre_idioma', 'tipo', 'descripcion'];

        foreach ($catalog['stalls'] as $stall) {
            $this->assertSame([], array_diff(array_keys($stall), $stallKeys));
            $this->assertArrayNotHasKey('contacto', $stall);
            $this->assertArrayNotHasKey('telefono', $stall);
            $this->assertArrayNotHasKey('imagen', $stall);

            if ($stall['caseta_padre'] !== null) {
                $this->assertContains($stall['caseta_padre'], $stallCodes);
            }
        }

        $translationBusinessKeys = [];
        foreach ($catalog['translations'] as $translation) {
            $this->assertSame([], array_diff(array_keys($translation), $translationKeys));
            $this->assertContains($translation['puesto_id'], $stallIds);
            $translationBusinessKeys[] = $translation['puesto_id'] . ':' . $translation['codigo_idioma'];
        }

        $this->assertCount(count($translationBusinessKeys), array_unique($translationBusinessKeys));
    }
}
