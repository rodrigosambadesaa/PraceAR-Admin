<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Security\PasswordPolicy;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PasswordPolicyTest extends TestCase
{
    public function test_generated_passwords_meet_the_server_policy(): void
    {
        $password = PasswordPolicy::generate(24, 'admin');

        PasswordPolicy::validate($password, 'admin');
        $this->assertSame(24, strlen($password));
    }

    public function test_predictable_password_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PasswordPolicy::validate('Abcdef123456!!!x', 'operator');
    }
}
