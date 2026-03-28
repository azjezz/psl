<?php

declare(strict_types=1);

namespace Psl\IRI\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\IP;
use Psl\IRI\Internal\HostConverter;
use Psl\URI\Authority\IPHost;
use Psl\URI\Authority\RegisteredNameHost;

final class HostConverterTest extends TestCase
{
    public function testConvertToUnicodeReturnsIPHostUnchanged(): void
    {
        $host = new IPHost(IP\Address::v4('127.0.0.1'));

        $result = HostConverter::convertToUnicode($host);

        static::assertSame($host, $result);
    }

    public function testConvertToUnicodeReturnsIPv6HostUnchanged(): void
    {
        $host = new IPHost(IP\Address::v6('::1'));

        $result = HostConverter::convertToUnicode($host);

        static::assertSame($host, $result);
    }

    public function testConvertToUnicodeDecodesPunycodeHost(): void
    {
        $host = new RegisteredNameHost('xn--r8jz45g.jp');

        $result = HostConverter::convertToUnicode($host);

        static::assertInstanceOf(RegisteredNameHost::class, $result);
        static::assertSame('例え.jp', $result->name);
    }

    public function testConvertToUnicodeReturnsPlainASCIIHostUnchanged(): void
    {
        $host = new RegisteredNameHost('example.com');

        $result = HostConverter::convertToUnicode($host);

        static::assertSame($host, $result);
    }

    public function testConvertToAsciiReturnsIPHostUnchanged(): void
    {
        $host = new IPHost(IP\Address::v4('192.168.1.1'));

        $result = HostConverter::convertToAscii($host);

        static::assertSame($host, $result);
    }

    public function testConvertToAsciiReturnsIPv6HostUnchanged(): void
    {
        $host = new IPHost(IP\Address::v6('fe80::1'));

        $result = HostConverter::convertToAscii($host);

        static::assertSame($host, $result);
    }

    public function testConvertToAsciiEncodesUnicodeHost(): void
    {
        $host = new RegisteredNameHost('例え.jp');

        $result = HostConverter::convertToAscii($host);

        static::assertInstanceOf(RegisteredNameHost::class, $result);
        static::assertSame('xn--r8jz45g.jp', $result->name);
    }

    public function testConvertToAsciiReturnsPlainASCIIHostUnchanged(): void
    {
        $host = new RegisteredNameHost('example.com');

        $result = HostConverter::convertToAscii($host);

        static::assertSame($host, $result);
    }
}
