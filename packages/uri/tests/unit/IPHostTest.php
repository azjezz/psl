<?php

declare(strict_types=1);

namespace Psl\URI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\IP\Address;
use Psl\URI\Authority\IPHost;

final class IPHostTest extends TestCase
{
    public function testIPv4(): void
    {
        $host = new IPHost(address: Address::v4('192.168.1.1'));

        static::assertSame('192.168.1.1', $host->toString());
    }

    public function testIPv6(): void
    {
        $host = new IPHost(address: Address::v6('::1'));

        static::assertSame('[::1]', $host->toString());
    }

    public function testIPv6Long(): void
    {
        $host = new IPHost(address: Address::v6('2001:db8::1'));

        static::assertSame('[2001:db8::1]', $host->toString());
    }

    public function testIPv6WithZone(): void
    {
        $host = new IPHost(address: Address::v6('fe80::1'), zone: 'eth0');

        static::assertSame('[fe80::1%25eth0]', $host->toString());
    }

    public function testStringable(): void
    {
        $host = new IPHost(address: Address::v4('192.168.1.1'));

        static::assertSame('192.168.1.1', (string) $host);
    }

    public function testIPv4Loopback(): void
    {
        $host = new IPHost(address: Address::v4('127.0.0.1'));

        static::assertSame('127.0.0.1', $host->toString());
    }

    public function testIPv6Loopback(): void
    {
        $host = new IPHost(address: Address::v6('::1'));

        static::assertSame('[::1]', $host->toString());
    }

    public function testIPv6FullExpansion(): void
    {
        $host = new IPHost(address: Address::v6('2001:0db8:0000:0000:0000:0000:0000:0001'));

        static::assertSame('[2001:db8::1]', $host->toString());
    }

    public function testIPv6AllZeros(): void
    {
        $host = new IPHost(address: Address::v6('::'));

        static::assertSame('[::]', $host->toString());
    }

    public function testIPv6AllOnes(): void
    {
        $host = new IPHost(address: Address::v6('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'));

        static::assertSame('[ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff]', $host->toString());
    }

    public function testZoneIdWithSpecialChars(): void
    {
        $host = new IPHost(address: Address::v6('fe80::1'), zone: 'en0');

        static::assertSame('[fe80::1%25en0]', $host->toString());
    }

    public function testZoneIdWithNumericInterface(): void
    {
        $host = new IPHost(address: Address::v6('fe80::1'), zone: '1');

        static::assertSame('[fe80::1%251]', $host->toString());
    }

    public function testIPv4Broadcast(): void
    {
        $host = new IPHost(address: Address::v4('255.255.255.255'));

        static::assertSame('255.255.255.255', $host->toString());
    }

    public function testIPv4Zeros(): void
    {
        $host = new IPHost(address: Address::v4('0.0.0.0'));

        static::assertSame('0.0.0.0', $host->toString());
    }

    public function testIPv6LinkLocalWithZone(): void
    {
        $host = new IPHost(address: Address::v6('fe80::1'), zone: 'eth0.100');

        static::assertSame('[fe80::1%25eth0.100]', $host->toString());
    }
}
