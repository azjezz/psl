<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Psl\SMTP\Exception\AuthenticationException;
use Psl\SMTP\Exception\ExceptionInterface;
use Psl\SMTP\Exception\RuntimeException;

final class AuthenticationExceptionTest extends TestCase
{
    public function testImplementsExceptionInterface(): void
    {
        $e = AuthenticationException::forUnsupportedMechanism('PLAIN');

        static::assertInstanceOf(ExceptionInterface::class, $e);
        static::assertInstanceOf(RuntimeException::class, $e);
    }

    public function testForRejected(): void
    {
        $e = AuthenticationException::forRejected('PLAIN', 535, 'Authentication failed');

        static::assertStringContainsString('PLAIN', $e->getMessage());
        static::assertStringContainsString('535', $e->getMessage());
        static::assertStringContainsString('Authentication failed', $e->getMessage());
    }

    public function testForRejectedLogin(): void
    {
        $e = AuthenticationException::forRejected('LOGIN', 535, 'Bad credentials');

        static::assertStringContainsString('LOGIN', $e->getMessage());
        static::assertStringContainsString('535', $e->getMessage());
        static::assertStringContainsString('Bad credentials', $e->getMessage());
    }

    public function testForRejectedXOAuth2(): void
    {
        $e = AuthenticationException::forRejected('XOAUTH2', 535, 'Invalid token');

        static::assertStringContainsString('XOAUTH2', $e->getMessage());
        static::assertStringContainsString('535', $e->getMessage());
        static::assertStringContainsString('Invalid token', $e->getMessage());
    }

    public function testForUnsupportedMechanism(): void
    {
        $e = AuthenticationException::forUnsupportedMechanism('PLAIN');

        static::assertStringContainsString('PLAIN', $e->getMessage());
        static::assertStringContainsString('does not support', $e->getMessage());
    }

    public function testForUnsupportedMechanismLogin(): void
    {
        $e = AuthenticationException::forUnsupportedMechanism('LOGIN');

        static::assertStringContainsString('LOGIN', $e->getMessage());
    }

    public function testForUnsupportedMechanismXOAuth2(): void
    {
        $e = AuthenticationException::forUnsupportedMechanism('XOAUTH2');

        static::assertStringContainsString('XOAUTH2', $e->getMessage());
    }

    public function testForRejectedWith235Code(): void
    {
        $e = AuthenticationException::forRejected('PLAIN', 504, 'Unrecognized type');

        static::assertStringContainsString('504', $e->getMessage());
    }
}
