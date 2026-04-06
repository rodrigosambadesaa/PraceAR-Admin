<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_public_legacy_aliases_render_successfully(): void
    {
        $this->get('/')->assertOk();
        $this->get('/index.php')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/login.php')->assertOk();
    }
}
