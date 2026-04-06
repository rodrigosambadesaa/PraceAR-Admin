<?php

namespace Tests\Feature;

use Tests\TestCase;

class UnityEndpointsTest extends TestCase
{
    public function test_unity_connection_endpoint_returns_compatibility_payload(): void
    {
        $this->get('/unity/connection.php')
            ->assertOk()
            ->assertJson([
                'codigo' => 202,
                'mensaje' => 'Conexión disponible.',
            ]);
    }

    public function test_unity_get_info_puesto_requires_id(): void
    {
        $this->get('/unity/get_info_puesto.php')
            ->assertOk()
            ->assertJson([
                'codigo' => 402,
                'mensaje' => 'Faltan datos para ejecutar la acción solicitada',
            ]);
    }

    public function test_unity_get_info_nave_requires_id_nave(): void
    {
        $this->get('/unity/get_info_nave_9.php')
            ->assertOk()
            ->assertJson([
                'codigo' => 402,
                'mensaje' => 'Faltan datos para ejecutar la acción solicitada',
            ]);
    }
}
