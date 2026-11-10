<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class InsecureDirectObjectReferenceTest extends TestCase
{
    use RefreshDatabase;

    private int $restrictedId;
    private int $otherId;
    private int $ownedStall;
    private int $otherStall;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('naves')->insert([
            ['id' => 1, 'tipo' => 'Uno'],
            ['id' => 2, 'tipo' => 'Dos'],
        ]);
        $this->restrictedId = DB::table('usuarios')->insertGetId([
            'login' => 'restricted',
            'password' => 'test-only',
        ]);
        $this->otherId = DB::table('usuarios')->insertGetId([
            'login' => 'other',
            'password' => 'test-only',
        ]);
        DB::table('usuario_naves')->insert([
            ['usuario_id' => $this->restrictedId, 'nave_id' => 1],
            ['usuario_id' => $this->otherId, 'nave_id' => 2],
        ]);
        $this->ownedStall = $this->stall('CE001', 1, 'Propio');
        $this->otherStall = $this->stall('CE002', 2, 'Ajeno');
    }

    public function test_new_accounts_have_no_implicit_administrator_access(): void
    {
        $this->assertDatabaseHas('usuarios', ['id' => $this->restrictedId, 'es_superadmin' => false]);

        DB::table('usuario_naves')->where('usuario_id', $this->restrictedId)->delete();

        $this->asUser($this->restrictedId)->get('/admin/edit.php?id=' . $this->ownedStall)->assertNotFound();
    }

    public function test_denies_cross_nave_reads_on_modern_and_legacy_routes(): void
    {
        $this->asUser($this->restrictedId)->get('/admin/edit.php?id=' . $this->otherStall)->assertNotFound();
        $this->asUser($this->restrictedId)->get('/?page=edit&id=' . $this->otherStall)->assertNotFound();
        $this->asUser($this->restrictedId)->get('/admin/edit_translations.php?id=' . $this->otherStall . '&codigo_idioma=gl')->assertNotFound();
        $this->asUser($this->restrictedId)->get('/?page=language&id=' . $this->otherStall . '&codigo_idioma=gl')->assertNotFound();
        $this->asUser($this->restrictedId)->postJson('/admin/ajax_quick_edit.php', [
            'id' => $this->otherStall,
            'field' => 'nombre',
        ])->assertNotFound();
    }

    public function test_denies_cross_nave_writes_including_images_and_translations(): void
    {
        $this->asUser($this->restrictedId)->post('/admin/edit.php?id=' . $this->otherStall, $this->editPayload())->assertNotFound();
        $this->asUser($this->restrictedId)->post('/?page=edit&id=' . $this->otherStall, $this->editPayload())->assertNotFound();
        $this->asUser($this->restrictedId)->post('/admin/edit_translations.php?id=' . $this->otherStall . '&codigo_idioma=gl', [
            'tipo' => 'Robado',
            'descripcion' => 'Modificado',
        ])->assertNotFound();
        $this->asUser($this->restrictedId)->post('/?page=language&id=' . $this->otherStall . '&codigo_idioma=gl', [
            'tipo' => 'Robado',
            'descripcion' => 'Modificado',
        ])->assertNotFound();

        foreach ([
            ['field' => 'nombre', 'value' => 'Robado'],
            ['field' => 'traduccion', 'tipo' => 'Robado', 'descripcion' => 'Modificado', 'codigo_idioma' => 'gl'],
            ['field' => 'imagen', 'eliminar_imagen' => '1'],
        ] as $payload) {
            $this->asUser($this->restrictedId)->post('/admin/ajax_quick_edit_save.php', [
                '_token' => 'idor-test-token',
                'csrf' => 'idor-test-token',
                'id' => $this->otherStall,
                ...$payload,
            ])->assertNotFound();
        }

        $this->assertDatabaseHas('puestos', ['id' => $this->otherStall, 'nombre' => 'Ajeno']);
        $this->assertDatabaseHas('puestos_traducciones', ['puesto_id' => $this->otherStall, 'tipo' => 'Original']);
    }

    public function test_denies_moving_an_owned_stall_to_an_unauthorized_nave(): void
    {
        $this->asUser($this->restrictedId)->post('/admin/edit.php?id=' . $this->ownedStall, [
            ...$this->editPayload(),
            'id_nave' => 2,
        ])->assertSessionHasErrors('id_nave');

        $this->assertDatabaseHas('puestos', ['id' => $this->ownedStall, 'id_nave' => 1]);
    }

    public function test_denies_linking_unauthorized_parent_stall(): void
    {
        $this->asUser($this->restrictedId)->post('/admin/edit.php?id=' . $this->ownedStall, [
            ...$this->editPayload(),
            'caseta_padre' => 'CE002',
        ])->assertSessionHasErrors('caseta_padre');

        $this->asUser($this->restrictedId)->post('/admin/ajax_quick_edit_save.php', [
            '_token' => 'idor-test-token',
            'csrf' => 'idor-test-token',
            'id' => $this->ownedStall,
            'field' => 'caseta_padre',
            'value' => 'CE002',
        ])->assertStatus(422);

        $this->assertDatabaseHas('puestos', ['id' => $this->ownedStall, 'caseta_padre' => null]);
    }

    public function test_scoped_index_and_suggestions_do_not_enumerate_other_naves(): void
    {
        $this->asUser($this->restrictedId)->get('/admin/index.php?lang=gl')
            ->assertOk()
            ->assertSee('CE001')
            ->assertDontSee('CE002');

        $this->asUser($this->restrictedId)->getJson('/admin/ajax_sugerencias.php?caseta=CE0&lang=gl')
            ->assertOk()
            ->assertExactJson(['CE001']);
    }

    public function test_allowed_user_can_read_own_stall_and_admin_can_read_all(): void
    {
        $this->asUser($this->restrictedId)->get('/admin/edit.php?id=' . $this->ownedStall)->assertOk();

        DB::table('usuarios')->where('id', $this->otherId)->update(['es_superadmin' => true]);
        $this->asUser($this->otherId)->get('/admin/edit.php?id=' . $this->ownedStall)->assertOk();
    }

    public function test_revoked_nave_access_is_enforced_immediately(): void
    {
        $this->asUser($this->restrictedId)->get('/admin/edit.php?id=' . $this->ownedStall)->assertOk();
        DB::table('usuario_naves')->where('usuario_id', $this->restrictedId)->delete();
        $this->asUser($this->restrictedId)->get('/admin/edit.php?id=' . $this->ownedStall)->assertNotFound();
    }

    private function stall(string $code, int $nave, string $name): int
    {
        $id = DB::table('puestos')->insertGetId([
            'caseta' => $code,
            'id_nave' => $nave,
            'nombre' => $name,
            'tipo_unity' => 'default',
            'activo' => true,
        ]);
        DB::table('puestos_traducciones')->insert([
            'puesto_id' => $id,
            'codigo_idioma' => 'gl',
            'nombre_idioma' => 'Galego',
            'tipo' => 'Original',
            'descripcion' => 'Descrición',
        ]);

        return $id;
    }

    private function editPayload(): array
    {
        return [
            'nombre' => 'No autorizado',
            'contacto' => '',
            'telefono' => '',
            'caseta_padre' => '',
            'id_nave' => 2,
            'tipo_unity' => 'default',
            'activo' => '1',
        ];
    }

    private function asUser(int $userId): self
    {
        return $this->withSession([
            '_token' => 'idor-test-token',
            'pracear.user_id' => $userId,
            'pracear.username' => 'idor-test',
            'pracear.issued_at' => time(),
            'pracear.last_activity' => time(),
        ]);
    }
}
