<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    public function test_admin_pages_redirect_when_session_is_missing(): void
    {
        $this->get('/admin/index.php')->assertOk();
        $this->get('/admin/market_sections.php')->assertRedirect('/?lang=gl');
        $this->get('/admin/change_password.php')->assertRedirect('/?lang=gl');
        $this->get('/admin/edit.php?id=1')->assertRedirect('/?lang=gl');
        $this->get('/admin/edit_translations.php?id=1')->assertRedirect('/?lang=gl');
    }
}
