<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\IP;

use PHPUnit\Framework\TestCase;
use Psl\IP\Exception\InvalidArgumentException;
use Psl\IP\Family;

final class FamilyTest extends TestCase
{
    public function testV4ValueIsFour(): void
    {
        static::assertSame(4, Family::V4->value);
    }

    public function testV6ValueIsSixteen(): void
    {
        static::assertSame(16, Family::V6->value);
    }

    public function testFromIanaFamilyIpv4(): void
    {
        static::assertSame(Family::V4, Family::fromIanaFamily(1));
    }

    public function testFromIanaFamilyIpv6(): void
    {
        static::assertSame(Family::V6, Family::fromIanaFamily(2));
    }

    public function testFromIanaFamilyUnknownThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected IANA address family 1 (IPv4) or 2 (IPv6), got 99.');

        Family::fromIanaFamily(99);
    }

    public function testIanaFamilyIpv4(): void
    {
        static::assertSame(1, Family::V4->ianaFamily());
    }

    public function testIanaFamilyIpv6(): void
    {
        static::assertSame(2, Family::V6->ianaFamily());
    }

    public function testIanaFamilyRoundTrip(): void
    {
        static::assertSame(Family::V4, Family::fromIanaFamily(Family::V4->ianaFamily()));
        static::assertSame(Family::V6, Family::fromIanaFamily(Family::V6->ianaFamily()));
    }
}
