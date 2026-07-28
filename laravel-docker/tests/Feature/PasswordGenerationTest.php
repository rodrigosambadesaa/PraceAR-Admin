<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\AdminAjaxController;
use App\Security\PasswordPolicy;
use Illuminate\Http\Request;
use Tests\TestCase;

final class PasswordGenerationTest extends TestCase
{
    public function test_endpoint_generates_the_exact_requested_length_above_128(): void
    {
        $request = $this->authenticatedRequest(256);

        $response = app(AdminAjaxController::class)->generatePassword($request);
        $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($payload['success']);
        $this->assertSame(256, strlen($payload['password']));
        PasswordPolicy::validate($payload['password'], 'admin');
    }

    public function test_endpoint_rejects_lengths_above_the_policy_limit(): void
    {
        $request = $this->authenticatedRequest(PasswordPolicy::MAX_LENGTH + 1);

        $response = app(AdminAjaxController::class)->generatePassword($request);
        $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertFalse($payload['success']);
        $this->assertStringContainsString((string) PasswordPolicy::MAX_LENGTH, $payload['message']);
    }

    private function authenticatedRequest(int $length): Request
    {
        $request = Request::create('/ajax/generate_password.php', 'POST', ['length' => $length]);
        $session = app('session')->driver();
        $session->put([
            'pracear.user_id' => 1,
            'pracear.username' => 'admin',
        ]);
        $request->setLaravelSession($session);

        return $request;
    }
}
