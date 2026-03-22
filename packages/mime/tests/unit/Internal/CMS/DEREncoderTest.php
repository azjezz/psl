<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit\Internal\CMS;

use PHPUnit\Framework\TestCase;
use Psl\MIME\Internal\CMS\DEREncoder;
use Psl\Str;
use Psl\Str\Byte;

final class DEREncoderTest extends TestCase
{
    public function testTagShortLength(): void
    {
        $result = DEREncoder::tag(0x04, 'abc');

        static::assertSame("\x04\x03abc", $result);
    }

    public function testTagMediumLength(): void
    {
        $content = Str\repeat('A', 200);
        $result = DEREncoder::tag(0x04, $content);

        static::assertSame(0x04, Byte\ord($result[0]));
        static::assertSame(0x81, Byte\ord($result[1]));
        static::assertSame(200, Byte\ord($result[2]));
        static::assertSame($content, Byte\slice($result, 3));
    }

    public function testTagLongLength(): void
    {
        $content = Str\repeat('A', 300);
        $result = DEREncoder::tag(0x04, $content);

        static::assertSame(0x04, Byte\ord($result[0]));
        static::assertSame(0x82, Byte\ord($result[1]));
        static::assertSame(1, Byte\ord($result[2]));
        static::assertSame(44, Byte\ord($result[3]));
        static::assertSame($content, Byte\slice($result, 4));
    }

    public function testIntegerStripsLeadingZeros(): void
    {
        $result = DEREncoder::integer("\x00\x00\x42");

        static::assertSame("\x02\x01\x42", $result);
    }

    public function testIntegerAddsSignPadding(): void
    {
        $result = DEREncoder::integer("\x80");

        static::assertSame("\x02\x02\x00\x80", $result);
    }

    public function testIntegerNoModification(): void
    {
        $result = DEREncoder::integer("\x42");

        static::assertSame("\x02\x01\x42", $result);
    }

    public function testSequence(): void
    {
        $result = DEREncoder::sequence('abc');

        static::assertSame("\x30\x03abc", $result);
    }

    public function testSet(): void
    {
        $result = DEREncoder::set('abc');

        static::assertSame("\x31\x03abc", $result);
    }

    public function testOctetString(): void
    {
        $result = DEREncoder::octetString('abc');

        static::assertSame("\x04\x03abc", $result);
    }

    public function testObjectIdentifier(): void
    {
        $oidBytes = "\x2a\x86\x48";
        $result = DEREncoder::objectIdentifier($oidBytes);

        static::assertSame("\x06\x03\x2a\x86\x48", $result);
    }

    public function testBitString(): void
    {
        $result = DEREncoder::bitString('abc');

        static::assertSame("\x03\x04\x00abc", $result);
    }

    public function testContextTagConstructed(): void
    {
        $result = DEREncoder::contextTag(0, 'abc');

        static::assertSame("\xa0\x03abc", $result);
    }

    public function testContextTagPrimitive(): void
    {
        $result = DEREncoder::contextTag(0, 'abc', constructed: false);

        static::assertSame("\x80\x03abc", $result);
    }

    public function testNull(): void
    {
        static::assertSame("\x05\x00", DEREncoder::null());
    }

    public function testBoolean(): void
    {
        static::assertSame("\x01\x01\xff", DEREncoder::boolean(true));
        static::assertSame("\x01\x01\x00", DEREncoder::boolean(false));
    }

    public function testPemEncodeAndDecode(): void
    {
        $der = "\x30\x03\x02\x01\x42";
        $pem = DEREncoder::pemEncode($der, 'TEST');

        static::assertStringContainsString('-----BEGIN TEST-----', $pem);
        static::assertStringContainsString('-----END TEST-----', $pem);

        $decoded = DEREncoder::pemDecode($pem);
        static::assertSame($der, $decoded);
    }

    public function testPemDecodeInvalidReturnsEmpty(): void
    {
        static::assertSame('', DEREncoder::pemDecode('not valid base64!!!'));
    }

    public function testUtcTime(): void
    {
        $result = DEREncoder::utcTime('240101120000Z');

        static::assertSame(0x17, Byte\ord($result[0]));
        static::assertSame(13, Byte\ord($result[1]));
    }

    public function testPrintableString(): void
    {
        $result = DEREncoder::printableString('Hello');

        static::assertSame(0x13, Byte\ord($result[0]));
        static::assertSame(5, Byte\ord($result[1]));
    }

    public function testUtf8String(): void
    {
        $result = DEREncoder::utf8String('Hello');

        static::assertSame(0x0C, Byte\ord($result[0]));
        static::assertSame(5, Byte\ord($result[1]));
    }
}
