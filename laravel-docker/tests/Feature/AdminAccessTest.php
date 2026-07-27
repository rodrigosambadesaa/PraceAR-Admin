<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    public function test_admin_pages_redirect_when_session_is_missing(): void
    {
        $this->get('/admin/index.php')->assertRedirect('/login');
        $this->get('/admin/market_sections.php')->assertRedirect('/login');
        $this->get('/admin/change_password.php')->assertRedirect('/login');
        $this->get('/admin/edit.php?id=1')->assertRedirect('/login');
        $this->get('/admin/edit_translations.php?id=1')->assertRedirect('/login');
    }

    public function test_logout_cannot_be_triggered_with_get(): void
    {
        $this->get('/admin/logout.php')->assertMethodNotAllowed();
    }
}
