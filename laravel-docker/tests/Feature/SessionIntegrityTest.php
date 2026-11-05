<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SessionIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userId = DB::table('usuarios')->insertGetId([
            'login' => 'security_test',
            'password' => 'test-only-password-hash',
        ]);
    }

    public function test_expired_absolute_session_cannot_read_legacy_admin_page(): void
    {
        $response = $this->withAdminSession(time() - 9 * 3600, time() - 10)->get('/?page=market_sections');

        $response->assertOk()->assertSee('name="login"', false);
        $response->assertSessionMissing('pracear.user_id');
    }

    public function test_expired_idle_session_cannot_update_data_via_legacy_root_post(): void
    {
        DB::table('naves')->insert(['id' => 1, 'tipo' => 'Mercado']);
        $stallId = DB::table('puestos')->insertGetId([
            'caseta' => 'CE001',
            'nombre' => 'Puesto',
            'id_nave' => 1,
        ]);
        DB::table('puestos_traducciones')->insert([
            'puesto_id' => $stallId,
            'codigo_idioma' => 'gl',
            'tipo' => 'Original',
        ]);

        $this->withAdminSession(time() - 3600, time() - 1900)
            ->post('/?page=language&id=' . $stallId . '&codigo_idioma=gl', [
                'tipo' => 'Modificado',
                'descripcion' => 'Acceso no autorizado',
            ])
            ->assertRedirect('/login?lang=gl');

        $this->assertDatabaseHas('puestos_traducciones', [
            'puesto_id' => $stallId,
            'codigo_idioma' => 'gl',
            'tipo' => 'Original',
        ]);
    }

    public function test_session_without_server_side_timestamps_is_rejected(): void
    {
        $this->withSession(['pracear.user_id' => $this->userId, 'pracear.username' => 'security_test'])
            ->get('/?page=market_sections')
            ->assertOk()
            ->assertSee('name="login"', false);
    }

    public function test_deleted_user_cannot_use_existing_session(): void
    {
        DB::table('usuarios')->where('id', $this->userId)->delete();

        $this->withAdminSession(time() - 30, time() - 5)
            ->get('/?page=market_sections')
            ->assertOk()
            ->assertSee('name="login"', false);
    }

    public function test_active_session_can_read_admin_page(): void
    {
        $this->withAdminSession(time() - 300, time() - 60)
            ->get('/?page=market_sections')
            ->assertOk()
            ->assertDontSee('name="login"', false)
            ->assertSessionHas('pracear.last_activity');
    }

    private function withAdminSession(int $issuedAt, int $lastActivity): self
    {
        return $this->withSession([
            'pracear.user_id' => $this->userId,
            'pracear.username' => 'security_test',
            'pracear.issued_at' => $issuedAt,
            'pracear.last_activity' => $lastActivity,
        ]);
    }
}
