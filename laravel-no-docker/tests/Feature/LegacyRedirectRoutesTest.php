<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegacyRedirectRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('transition.legacy_base_url', 'https://legacy.example.test');
        config()->set('transition.legacy_paths.home', 'index.php');
        config()->set('transition.legacy_paths.login', 'login.php');
        config()->set('transition.legacy_paths.admin', 'admin/index.php');
    }

    public function test_transition_home_is_available(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Transicion gradual a Laravel');
    }

    public function test_legacy_home_redirects_to_legacy_php(): void
    {
        $response = $this->get('/legacy');

        $response->assertRedirect('https://legacy.example.test/index.php');
    }

    public function test_legacy_login_redirects_to_legacy_php(): void
    {
        $response = $this->get('/legacy/login');

        $response->assertRedirect('https://legacy.example.test/login.php');
    }

    public function test_legacy_admin_root_redirects_to_legacy_admin_index(): void
    {
        $response = $this->get('/legacy/admin');

        $response->assertRedirect('https://legacy.example.test/admin/index.php');
    }

    public function test_legacy_admin_nested_path_redirects_to_legacy_admin_subpath(): void
    {
        $response = $this->get('/legacy/admin/ajax_quick_edit.php');

        $response->assertRedirect('https://legacy.example.test/admin/ajax_quick_edit.php');
    }

    public function test_legacy_admin_blocks_path_traversal(): void
    {
        $response = $this->get('/legacy/admin/../connection.php');

        $response->assertNotFound();
    }
}