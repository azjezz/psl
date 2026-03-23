<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Internal;

use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Psl\Binary\Writer;
use Psl\DNS\EDNS\CookieOption;
use Psl\DNS\EDNS\ECSOption;
use Psl\DNS\EDNS\ExtendedDNSErrorOption;
use Psl\DNS\EDNS\KeyTagOption;
use Psl\DNS\EDNS\NSIDOption;
use Psl\DNS\EDNS\PaddingOption;
use Psl\DNS\EDNS\RawOption;
use Psl\DNS\EDNS\TCPKeepaliveOption;
use Psl\DNS\Internal\EDNS\EDNSCodec;
use Psl\IP\Address;
use Psl\IP\Family;

final class EdnsCodecTest extends TestCase
{
    public function testDecodeEmptyData(): void
    {
        $options = EDNSCodec::decodeOptions('');

        static::assertSame([], $options);
    }

    public function testDecodeEcsOptionIpv4(): void
    {
        $wire = new Writer()
            ->u16(8)
            ->u16(7)
            ->u16(1)
            ->u8(24)
            ->u8(0)
            ->bytes("\xC6\x33\x64")
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $ecs = $options[0];
        static::assertInstanceOf(ECSOption::class, $ecs);
        static::assertSame(8, $ecs->code);
        static::assertSame(Family::V4, $ecs->address->family);
        static::assertSame(24, $ecs->sourcePrefixLength);
        static::assertSame(0, $ecs->scopePrefixLength);
        static::assertSame('198.51.100.0', $ecs->address->toString());
    }

    public function testDecodeEcsOptionIpv6(): void
    {
        $wire = new Writer()
            ->u16(8)
            ->u16(10)
            ->u16(2)
            ->u8(48)
            ->u8(0)
            ->bytes("\x20\x01\x0D\xB8\x00\x00")
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $ecs = $options[0];
        static::assertInstanceOf(ECSOption::class, $ecs);
        static::assertSame(Family::V6, $ecs->address->family);
        static::assertSame(48, $ecs->sourcePrefixLength);
        static::assertSame(0, $ecs->scopePrefixLength);
        static::assertSame('2001:db8::', $ecs->address->toString());
    }

    public function testDecodeUnknownOptionCode(): void
    {
        $wire = new Writer()
            ->u16(99)
            ->u16(2)
            ->u8(0xAB)
            ->u8(0xCD)
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $raw = $options[0];
        static::assertInstanceOf(RawOption::class, $raw);
        static::assertSame(99, $raw->code);
        static::assertSame("\xAB\xCD", $raw->data);
    }

    public function testDecodeMultipleOptions(): void
    {
        $wire = new Writer()
            ->u16(8)
            ->u16(7)
            ->u16(1)
            ->u8(24)
            ->u8(0)
            ->bytes("\xC6\x33\x64")
            ->u16(99)
            ->u16(3)
            ->bytes("\x01\x02\x03")
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(2, $options);
        static::assertInstanceOf(ECSOption::class, $options[0]);
        $raw = $options[1];
        static::assertInstanceOf(RawOption::class, $raw);
        static::assertSame(99, $raw->code);
        static::assertSame("\x01\x02\x03", $raw->data);
    }

    public function testDecodeUnknownOptionWithEmptyData(): void
    {
        $wire = new Writer()
            ->u16(100)
            ->u16(0)
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $raw = $options[0];
        static::assertInstanceOf(RawOption::class, $raw);
        static::assertSame(100, $raw->code);
        static::assertSame('', $raw->data);
    }

    public function testEncodeEmptyOptions(): void
    {
        $result = EDNSCodec::encodeOptions([]);

        static::assertSame('', $result);
    }

    public function testEncodeDecodeRoundTrip(): void
    {
        $options = [
            new ECSOption(Address::v4('198.51.100.0'), 24, 0),
            new RawOption(99, "\xAB\xCD"),
        ];

        $wire = EDNSCodec::encodeOptions($options);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(2, $decoded);

        $ecs = $decoded[0];
        static::assertInstanceOf(ECSOption::class, $ecs);
        static::assertSame(Family::V4, $ecs->address->family);
        static::assertSame(24, $ecs->sourcePrefixLength);
        static::assertSame(0, $ecs->scopePrefixLength);
        static::assertSame('198.51.100.0', $ecs->address->toString());

        $raw = $decoded[1];
        static::assertInstanceOf(RawOption::class, $raw);
        static::assertSame(99, $raw->code);
        static::assertSame("\xAB\xCD", $raw->data);
    }

    public function testDecodeEcsWithScopePrefix(): void
    {
        $wire = new Writer()
            ->u16(8)
            ->u16(7)
            ->u16(1)
            ->u8(24)
            ->u8(20)
            ->bytes("\xC6\x33\x64")
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $ecs = $options[0];
        static::assertInstanceOf(ECSOption::class, $ecs);
        static::assertSame(24, $ecs->sourcePrefixLength);
        static::assertSame(20, $ecs->scopePrefixLength);
    }

    public function testDecodeCookieClientOnly(): void
    {
        $wire = new Writer()
            ->u16(10)
            ->u16(8)
            ->bytes("\x01\x02\x03\x04\x05\x06\x07\x08")
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $cookie = $options[0];
        static::assertInstanceOf(CookieOption::class, $cookie);
        static::assertSame(10, $cookie->code);
        static::assertSame("\x01\x02\x03\x04\x05\x06\x07\x08", $cookie->clientCookie);
        static::assertSame('', $cookie->serverCookie);
    }

    public function testDecodeCookieWithServerCookie(): void
    {
        $wire = new Writer()
            ->u16(10)
            ->u16(16)
            ->bytes("\x01\x02\x03\x04\x05\x06\x07\x08")
            ->bytes("\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8")
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $cookie = $options[0];
        static::assertInstanceOf(CookieOption::class, $cookie);
        static::assertSame("\x01\x02\x03\x04\x05\x06\x07\x08", $cookie->clientCookie);
        static::assertSame("\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8", $cookie->serverCookie);
    }

    public function testDecodeEcsWithZeroPrefixLength(): void
    {
        $wire = new Writer()
            ->u16(8)
            ->u16(4)
            ->u16(1)
            ->u8(0)
            ->u8(0)
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $ecs = $options[0];
        static::assertInstanceOf(ECSOption::class, $ecs);
        static::assertSame(0, $ecs->sourcePrefixLength);
        static::assertSame('0.0.0.0', $ecs->address->toString());
    }

    public function testDecodeNsid(): void
    {
        $wire = new Writer()
            ->u16(3)
            ->u16(5)
            ->bytes('ns1ab')
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $nsid = $options[0];
        static::assertInstanceOf(NSIDOption::class, $nsid);
        static::assertSame(3, $nsid->code);
        static::assertSame('ns1ab', $nsid->id);
    }

    public function testDecodeNsidEmpty(): void
    {
        $wire = new Writer()
            ->u16(3)
            ->u16(0)
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $nsid = $options[0];
        static::assertInstanceOf(NSIDOption::class, $nsid);
        static::assertSame('', $nsid->id);
    }

    public function testDecodeTcpKeepaliveWithTimeout(): void
    {
        $wire = new Writer()
            ->u16(11)
            ->u16(2)
            ->u16(6000)
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $keepalive = $options[0];
        static::assertInstanceOf(TCPKeepaliveOption::class, $keepalive);
        static::assertSame(11, $keepalive->code);
        static::assertSame(6000, $keepalive->timeout);
    }

    public function testDecodeTcpKeepaliveEmpty(): void
    {
        $wire = new Writer()
            ->u16(11)
            ->u16(0)
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $keepalive = $options[0];
        static::assertInstanceOf(TCPKeepaliveOption::class, $keepalive);
        static::assertNull($keepalive->timeout);
    }

    public function testDecodePadding(): void
    {
        $wire = new Writer()
            ->u16(12)
            ->u16(8)
            ->bytes("\x00\x00\x00\x00\x00\x00\x00\x00")
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $padding = $options[0];
        static::assertInstanceOf(PaddingOption::class, $padding);
        static::assertSame(12, $padding->code);
        static::assertSame(8, $padding->length);
    }

    public function testDecodeKeyTag(): void
    {
        $wire = new Writer()
            ->u16(14)
            ->u16(4)
            ->u16(20_326)
            ->u16(19_036)
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $keyTag = $options[0];
        static::assertInstanceOf(KeyTagOption::class, $keyTag);
        static::assertSame(14, $keyTag->code);
        static::assertSame([20_326, 19_036], $keyTag->tags);
    }

    public function testDecodeExtendedDnsError(): void
    {
        $wire = new Writer()
            ->u16(15)
            ->u16(7)
            ->u16(6)
            ->bytes('bogus')
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $ede = $options[0];
        static::assertInstanceOf(ExtendedDNSErrorOption::class, $ede);
        static::assertSame(15, $ede->code);
        static::assertSame(6, $ede->infoCode);
        static::assertSame('bogus', $ede->extraText);
    }

    public function testDecodeExtendedDnsErrorCodeOnly(): void
    {
        $wire = new Writer()
            ->u16(15)
            ->u16(2)
            ->u16(23)
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $options);
        $ede = $options[0];
        static::assertInstanceOf(ExtendedDNSErrorOption::class, $ede);
        static::assertSame(23, $ede->infoCode);
        static::assertSame('', $ede->extraText);
    }

    public function testDecodeOptionLengthOverrunThrows(): void
    {
        $wire = new Writer()
            ->u16(99)
            ->u16(100)
            ->bytes("\xAB\xCD")
            ->toString();

        $this->expectException(OutOfBoundsException::class);

        EDNSCodec::decodeOptions($wire);
    }

    public function testDecodeOptionLengthZeroWithData(): void
    {
        $wire = new Writer()
            ->u16(99)
            ->u16(0)
            ->u16(99)
            ->u16(2)
            ->bytes("\xAB\xCD")
            ->toString();

        $options = EDNSCodec::decodeOptions($wire);

        static::assertCount(2, $options);
        static::assertInstanceOf(RawOption::class, $options[0]);
        static::assertSame('', $options[0]->data);
        static::assertInstanceOf(RawOption::class, $options[1]);
        static::assertSame("\xAB\xCD", $options[1]->data);
    }
}
