<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\ValidateHost;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class SecurityHardeningTest extends TestCase
{
    public function test_login_response_has_defensive_headers_and_external_script_only_csp(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin')
            ->assertHeader('Cache-Control');

        $policy = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringContainsString("script-src 'self'", $policy);
        $this->assertStringNotContainsString("'nonce-", $policy);
        $this->assertStringNotContainsString("'unsafe-inline'", explode('style-src', $policy)[0]);
    }

    public function test_state_changing_ajax_routes_require_csrf(): void
    {
        // Laravel disables request-forgery protection automatically while the
        // application reports the "testing" environment.
        $this->app->detectEnvironment(static fn (): string => 'local');

        $this->postJson('/admin/ajax_quick_edit.php', ['id' => 1, 'field' => 'nombre'])
            ->assertStatus(419);

        $this->post('/helpers/verify_malicious_photo.php')
            ->assertStatus(419);
    }

    public function test_unknown_host_is_rejected(): void
    {
        $request = Request::create('http://attacker.invalid/login');

        try {
            app(ValidateHost::class)->handle($request, static fn () => response('unexpected'));
            $this->fail('The untrusted host was accepted.');
        } catch (HttpException $exception) {
            $this->assertSame(400, $exception->getStatusCode());
        }
    }
}
