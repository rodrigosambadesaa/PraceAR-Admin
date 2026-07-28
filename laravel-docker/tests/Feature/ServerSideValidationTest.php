<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\AdminAjaxController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ServerSideValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('naves')->insert(['id' => 1, 'tipo' => 'Mercado']);
        $userId = DB::table('usuarios')->insertGetId([
            'login' => 'admin_test',
            'password' => 'not-used-by-these-validation-tests',
        ]);
        $stallId = DB::table('puestos')->insertGetId([
            'caseta' => 'CE001',
            'nombre' => 'Puesto de prueba',
            'contacto' => 'Contacto',
            'telefono' => '600000000',
            'id_nave' => 1,
            'tipo_unity' => 'default',
            'activo' => true,
        ]);
        DB::table('puestos_traducciones')->insert([
            'puesto_id' => $stallId,
            'codigo_idioma' => 'es',
            'nombre_idioma' => 'Español',
            'tipo' => 'Artesanía',
            'descripcion' => 'Descripción de prueba',
        ]);

        $this->assertGreaterThan(0, $userId);
    }

    public function test_search_is_validated_without_javascript(): void
    {
        $response = $this->authenticatedPost('/?page=1', [
            'caseta' => '<script>',
            'lang' => 'es',
        ]);

        $response->assertRedirect()->assertSessionHasErrors('caseta');
    }

    public function test_stall_edit_is_validated_without_javascript(): void
    {
        $stall = DB::table('puestos')->first();
        $this->assertNotNull($stall);

        $response = $this->authenticatedPost('/?page=edit&id=' . (int) $stall->id, [
            'nombre' => (string) ($stall->nombre ?? ''),
            'contacto' => (string) ($stall->contacto ?? ''),
            'telefono' => 'abc@',
            'caseta_padre' => '',
            'tipo_unity' => (string) ($stall->tipo_unity ?? 'default'),
            'id_nave' => (int) $stall->id_nave,
        ]);

        $response->assertRedirect()->assertSessionHasErrors('telefono');
    }

    public function test_translation_is_validated_without_javascript(): void
    {
        $translation = DB::table('puestos_traducciones')->first();
        $this->assertNotNull($translation);

        $url = sprintf(
            '/?page=language&id=%d&codigo_idioma=%s',
            (int) $translation->puesto_id,
            rawurlencode((string) $translation->codigo_idioma),
        );
        $response = $this->authenticatedPost($url, [
            'tipo' => '',
            'descripcion' => 'Descripción válida',
        ]);

        $response->assertRedirect()->assertSessionHasErrors('tipo');
    }

    public function test_password_change_is_validated_without_javascript(): void
    {
        $tooLong = str_repeat('A', 1025);
        $response = $this->authenticatedPost('/?page=change_password', [
            'old_password' => $tooLong,
            'new_password' => 'Random-Valid9!$Example',
            'confirm_password' => 'Random-Valid9!$Example',
        ]);

        $response->assertRedirect()->assertSessionHasErrors('old_password');
    }

    public function test_quick_edit_is_validated_without_javascript(): void
    {
        $stallId = (int) DB::table('puestos')->value('id');
        $this->assertGreaterThan(0, $stallId);

        $request = Request::create('/admin/ajax_quick_edit_save.php', 'POST', [
            'id' => $stallId,
            'field' => 'nombre',
            'value' => '',
        ]);
        $session = app('session')->driver();
        $session->put([
            'pracear.user_id' => 1,
            'pracear.username' => 'admin',
        ]);
        $request->setLaravelSession($session);
        $response = app(AdminAjaxController::class)->quickEditSave($request);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertFalse((bool) json_decode((string) $response->getContent(), true)['success']);
    }

    private function authenticatedPost(string $uri, array $data)
    {
        return $this->withAdminSession()->post($uri, [
            '_token' => 'server-side-validation-token',
            ...$data,
        ]);
    }

    private function withAdminSession(): self
    {
        $user = DB::table('usuarios')->first();
        $this->assertNotNull($user);

        return $this->withSession([
            '_token' => 'server-side-validation-token',
            'pracear.user_id' => (int) $user->id,
            'pracear.username' => (string) $user->login,
            'pracear.issued_at' => time(),
            'pracear.last_activity' => time(),
        ]);
    }
}
