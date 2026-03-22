<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\SMTP\Security;

final class SecurityTest extends TestCase
{
    public function testValues(): void
    {
        static::assertSame('none', Security::None->value);
        static::assertSame('starttls', Security::StartTLS->value);
        static::assertSame('tls', Security::TLS->value);
    }

    public function testFromValue(): void
    {
        static::assertSame(Security::None, Security::from('none'));
        static::assertSame(Security::StartTLS, Security::from('starttls'));
        static::assertSame(Security::TLS, Security::from('tls'));
    }

    public function testTryFromValidValues(): void
    {
        static::assertSame(Security::None, Security::tryFrom('none'));
        static::assertSame(Security::StartTLS, Security::tryFrom('starttls'));
        static::assertSame(Security::TLS, Security::tryFrom('tls'));
    }

    public function testTryFromInvalidValues(): void
    {
        static::assertNull(Security::tryFrom(''));
        static::assertNull(Security::tryFrom('NONE'));
        static::assertNull(Security::tryFrom('TLS'));
        static::assertNull(Security::tryFrom('StartTLS'));
        static::assertNull(Security::tryFrom('ssl'));
        static::assertNull(Security::tryFrom('invalid'));
    }

    public function testCasesCount(): void
    {
        static::assertCount(3, Security::cases());
    }

    public function testCasesContainsAll(): void
    {
        $cases = Security::cases();

        static::assertContains(Security::None, $cases);
        static::assertContains(Security::StartTLS, $cases);
        static::assertContains(Security::TLS, $cases);
    }

    public function testEnumCaseName(): void
    {
        static::assertSame('None', Security::None->name);
        static::assertSame('StartTLS', Security::StartTLS->name);
        static::assertSame('TLS', Security::TLS->name);
    }
}
