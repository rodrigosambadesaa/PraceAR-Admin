<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Security\PwnedPasswordChecker;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

final class PwnedPasswordCheckerTest extends TestCase
{
    public function test_compromised_password_is_detected_with_k_anonymity(): void
    {
        $candidate = 'Known-Compromised-Password';
        $hash = strtoupper(sha1($candidate));
        $prefix = substr($hash, 0, 5);
        $suffix = substr($hash, 5);

        Http::fake([
            "https://api.pwnedpasswords.com/range/{$prefix}" => Http::response(
                "00000000000000000000000000000000000:1\r\n{$suffix}:42\r\n",
            ),
        ]);

        $this->assertTrue(app(PwnedPasswordChecker::class)->isCompromised($candidate));
        Http::assertSent(static function (Request $request) use ($prefix): bool {
            return $request->url() === "https://api.pwnedpasswords.com/range/{$prefix}"
                && $request->hasHeader('Add-Padding', 'true');
        });
    }

    public function test_unknown_password_is_not_marked_as_compromised(): void
    {
        Http::fake([
            '*' => Http::response('00000000000000000000000000000000000:1'),
        ]);

        $this->assertFalse(app(PwnedPasswordChecker::class)->isCompromised('Unique-Random-Password9!$'));
    }

    public function test_remote_failure_is_fail_closed(): void
    {
        Http::fake([
            '*' => Http::response('Unavailable', 503),
        ]);

        $this->expectException(RuntimeException::class);
        app(PwnedPasswordChecker::class)->isCompromised('Unique-Random-Password9!$');
    }
}
