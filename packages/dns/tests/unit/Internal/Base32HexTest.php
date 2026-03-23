<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\DNS\Exception\InvalidArgumentException;
use Psl\DNS\Internal\Base32Hex;

final class Base32HexTest extends TestCase
{
    public function testEncodeEmpty(): void
    {
        static::assertSame('', Base32Hex::encode(''));
    }

    public function testDecodeEmpty(): void
    {
        static::assertSame('', Base32Hex::decode(''));
    }

    public function testEncodeKnownValues(): void
    {
        static::assertSame('CO', Base32Hex::encode('f'));
        static::assertSame('CPNG', Base32Hex::encode('fo'));
        static::assertSame('CPNMU', Base32Hex::encode('foo'));
        static::assertSame('CPNMUOG', Base32Hex::encode('foob'));
        static::assertSame('CPNMUOJ1', Base32Hex::encode('fooba'));
        static::assertSame('CPNMUOJ1E8', Base32Hex::encode('foobar'));
    }

    public function testDecodeKnownValues(): void
    {
        static::assertSame('f', Base32Hex::decode('CO'));
        static::assertSame('fo', Base32Hex::decode('CPNG'));
        static::assertSame('foo', Base32Hex::decode('CPNMU'));
        static::assertSame('foob', Base32Hex::decode('CPNMUOG'));
        static::assertSame('fooba', Base32Hex::decode('CPNMUOJ1'));
        static::assertSame('foobar', Base32Hex::decode('CPNMUOJ1E8'));
    }

    public function testRoundTrip(): void
    {
        $binary = "\x00\x01\x02\xFF\xFE\xFD";
        $encoded = Base32Hex::encode($binary);
        $decoded = Base32Hex::decode($encoded);

        static::assertSame($binary, $decoded);
    }

    public function testDecodeCaseInsensitive(): void
    {
        static::assertSame('foo', Base32Hex::decode('cpnmu'));
        static::assertSame('foo', Base32Hex::decode('CPNMU'));
    }

    public function testDecodeWithPadding(): void
    {
        static::assertSame('foo', Base32Hex::decode('CPNMU==='));
    }

    public function testRoundTripAllByteAlignments(): void
    {
        static::assertSame("\x01", Base32Hex::decode(Base32Hex::encode("\x01")));
        static::assertSame("\x01\x02", Base32Hex::decode(Base32Hex::encode("\x01\x02")));
        static::assertSame("\x01\x02\x03", Base32Hex::decode(Base32Hex::encode("\x01\x02\x03")));
        static::assertSame("\x01\x02\x03\x04", Base32Hex::decode(Base32Hex::encode("\x01\x02\x03\x04")));
        static::assertSame("\x01\x02\x03\x04\x05", Base32Hex::decode(Base32Hex::encode("\x01\x02\x03\x04\x05")));
    }

    public function testEncodeHighBitValues(): void
    {
        $encoded = Base32Hex::encode("\xFF");

        static::assertSame('VS', $encoded);
        static::assertSame("\xFF", Base32Hex::decode($encoded));
    }

    public function testDecodeInvalidCharacterThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Base32Hex::decode('CPNMU!');
    }
}
