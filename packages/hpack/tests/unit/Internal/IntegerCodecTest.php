<?php

declare(strict_types=1);

namespace Psl\HPACK\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\HPACK\Exception\DecodingException;
use Psl\HPACK\Exception\IntegerOverflowException;
use Psl\HPACK\Internal\IntegerCodec;

use function strlen;

final class IntegerCodecTest extends TestCase
{
    public function testRfcC1Example10With5BitPrefix(): void
    {
        $encoded = IntegerCodec::encode(10, 5);

        static::assertSame("\x0a", $encoded);

        [$value, $offset] = IntegerCodec::decode($encoded, 0, 5);
        static::assertSame(10, $value);
        static::assertSame(1, $offset);
    }

    public function testRfcC1Example1337With5BitPrefix(): void
    {
        $encoded = IntegerCodec::encode(1337, 5);

        static::assertSame("\x1f\x9a\x0a", $encoded);

        [$value, $offset] = IntegerCodec::decode($encoded, 0, 5);
        static::assertSame(1337, $value);
        static::assertSame(3, $offset);
    }

    public function testRfcC1Example42With8BitPrefix(): void
    {
        $encoded = IntegerCodec::encode(42, 8);

        static::assertSame("\x2a", $encoded);

        [$value, $offset] = IntegerCodec::decode($encoded, 0, 8);
        static::assertSame(42, $value);
        static::assertSame(1, $offset);
    }

    public function testZeroValue(): void
    {
        foreach ([4, 5, 6, 7, 8] as $prefix) {
            $encoded = IntegerCodec::encode(0, $prefix);
            [$value, $offset] = IntegerCodec::decode($encoded, 0, $prefix);
            static::assertSame(0, $value);
            static::assertSame(1, $offset);
        }
    }

    public function testBoundaryJustBelowMaxPrefix(): void
    {
        foreach ([4, 5, 6, 7] as $prefix) {
            $boundary = (1 << $prefix) - 2;
            $encoded = IntegerCodec::encode($boundary, $prefix);
            static::assertSame(1, strlen($encoded));
            [$value, $offset] = IntegerCodec::decode($encoded, 0, $prefix);
            static::assertSame($boundary, $value);
            static::assertSame(1, $offset);
        }
    }

    public function testBoundaryAtMaxPrefix(): void
    {
        foreach ([4, 5, 6, 7] as $prefix) {
            $boundary = (1 << $prefix) - 1;
            $encoded = IntegerCodec::encode($boundary, $prefix);
            static::assertGreaterThan(1, strlen($encoded));
            [$value, $_offset] = IntegerCodec::decode($encoded, 0, $prefix);
            static::assertSame($boundary, $value);
        }
    }

    public function testBoundaryAboveMaxPrefix(): void
    {
        foreach ([4, 5, 6, 7] as $prefix) {
            $boundary = 1 << $prefix;
            $encoded = IntegerCodec::encode($boundary, $prefix);
            [$value, $offset] = IntegerCodec::decode($encoded, 0, $prefix);
            static::assertSame($boundary, $value);
        }
    }

    public function testPrefixBytePreserved(): void
    {
        $encoded = IntegerCodec::encode(10, 5, 0b1100_0000);

        static::assertSame(0b1100_1010, ord($encoded[0]));

        [$value, $offset] = IntegerCodec::decode($encoded, 0, 5);
        static::assertSame(10, $value);
    }

    public function testAllPrefixSizesRoundTrip(): void
    {
        foreach ([1, 2, 3, 4, 5, 6, 7, 8] as $prefix) {
            foreach ([0, 1, 126, 127, 128, 255, 256, 1000, 65_535] as $value) {
                $encoded = IntegerCodec::encode($value, $prefix);
                [$decoded, $_] = IntegerCodec::decode($encoded, 0, $prefix);
                static::assertSame($value, $decoded);
            }
        }
    }

    public function testDecodeAtOffset(): void
    {
        $encoded = IntegerCodec::encode(42, 8);
        $data = "\x00\x00" . $encoded;

        [$value, $offset] = IntegerCodec::decode($data, 2, 8);

        static::assertSame(42, $value);
        static::assertSame(3, $offset);
    }

    public function testDecodeOverflow(): void
    {
        $this->expectException(IntegerOverflowException::class);

        $data = "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x01";

        IntegerCodec::decode($data, 0, 7);
    }

    public function testDecodeTruncated(): void
    {
        $this->expectException(DecodingException::class);

        IntegerCodec::decode("\x1f\x9a", 0, 5);
    }

    public function testDecodeEmptyData(): void
    {
        $this->expectException(DecodingException::class);

        IntegerCodec::decode('', 0, 5);
    }

    public function testDecodeOffsetBeyondData(): void
    {
        $this->expectException(DecodingException::class);

        IntegerCodec::decode("\x0a", 1, 5);
    }

    public function testLargeValueRoundTrip(): void
    {
        $value = 1_000_000;
        $encoded = IntegerCodec::encode($value, 5);
        [$decoded, $_] = IntegerCodec::decode($encoded, 0, 5);
        static::assertSame($value, $decoded);
    }
}
