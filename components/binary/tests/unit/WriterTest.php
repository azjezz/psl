<?php

declare(strict_types=1);

namespace Psl\Binary\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Binary\BufferedWriterInterface;
use Psl\Binary\Endianness;
use Psl\Binary\Writer;
use Psl\Binary\WriterInterface;
use Psl\Default\DefaultInterface;

final class WriterTest extends TestCase
{
    public function testImplementsBufferedWriterInterface(): void
    {
        $writer = new Writer();
        static::assertInstanceOf(BufferedWriterInterface::class, $writer);
        static::assertInstanceOf(WriterInterface::class, $writer);
        static::assertInstanceOf(DefaultInterface::class, $writer);
    }

    public function testDefault(): void
    {
        $writer = Writer::default();
        static::assertInstanceOf(Writer::class, $writer);
        static::assertSame('', $writer->toString());

        // Verify it's usable for chaining
        $result = Writer::default()->u8(0x01)->u16(0x0203)->toString();
        static::assertSame("\x01\x02\x03", $result);
    }

    public function testEmptyWriter(): void
    {
        $writer = new Writer();
        static::assertSame('', $writer->toString());
        static::assertSame('', (string) $writer);
    }

    public function testImmutability(): void
    {
        $writer = new Writer();
        $writer2 = $writer->u8(42);
        static::assertNotSame($writer, $writer2);
        static::assertSame('', $writer->toString());
        static::assertSame("\x2A", $writer2->toString());
    }

    public function testU8(): void
    {
        $writer = new Writer();
        $result = $writer->u8(0)->u8(255);
        static::assertSame("\x00\xFF", $result->toString());
    }

    public function testU16BigEndian(): void
    {
        $writer = new Writer(endianness: Endianness::Big);
        $result = $writer->u16(0x0102);
        static::assertSame("\x01\x02", $result->toString());
    }

    public function testU16LittleEndian(): void
    {
        $writer = new Writer(endianness: Endianness::Little);
        $result = $writer->u16(0x0102);
        static::assertSame("\x02\x01", $result->toString());
    }

    public function testU16PerCallEndianness(): void
    {
        $writer = new Writer(endianness: Endianness::Big);
        $result = $writer->u16(0x0102, Endianness::Little);
        static::assertSame("\x02\x01", $result->toString());
    }

    public function testU32PerCallEndianness(): void
    {
        $writer = new Writer(endianness: Endianness::Big);
        $result = $writer->u32(0x0102_0304, Endianness::Little);
        static::assertSame("\x04\x03\x02\x01", $result->toString());
    }

    public function testU64PerCallEndianness(): void
    {
        $writer = new Writer(endianness: Endianness::Big);
        $result = $writer->u64(0x0102_0304_0506_0708, Endianness::Little);
        static::assertSame("\x08\x07\x06\x05\x04\x03\x02\x01", $result->toString());
    }

    public function testI16PerCallEndianness(): void
    {
        $writer = new Writer(endianness: Endianness::Big);
        $result = $writer->i16(256, Endianness::Little);
        static::assertSame("\x00\x01", $result->toString());
    }

    public function testI32PerCallEndianness(): void
    {
        $writer = new Writer(endianness: Endianness::Big);
        $result = $writer->i32(1, Endianness::Little);
        static::assertSame("\x01\x00\x00\x00", $result->toString());
    }

    public function testI64PerCallEndianness(): void
    {
        $writer = new Writer(endianness: Endianness::Big);
        $result = $writer->i64(1, Endianness::Little);
        static::assertSame("\x01\x00\x00\x00\x00\x00\x00\x00", $result->toString());
    }

    public function testF32PerCallEndianness(): void
    {
        $writer = new Writer(endianness: Endianness::Big);
        $result = $writer->f32(1.0, Endianness::Little);
        // IEEE 754: 1.0f = 0x3F800000, little-endian = "\x00\x00\x80\x3F"
        static::assertSame("\x00\x00\x80\x3F", $result->toString());
    }

    public function testF64PerCallEndianness(): void
    {
        $writer = new Writer(endianness: Endianness::Big);
        $result = $writer->f64(1.0, Endianness::Little);
        // IEEE 754: 1.0 = 0x3FF0000000000000, little-endian = "\x00\x00\x00\x00\x00\x00\xF0\x3F"
        static::assertSame("\x00\x00\x00\x00\x00\x00\xF0\x3F", $result->toString());
    }

    public function testU32(): void
    {
        $writer = new Writer();
        $result = $writer->u32(0x0102_0304);
        static::assertSame("\x01\x02\x03\x04", $result->toString());
    }

    public function testU64(): void
    {
        $writer = new Writer();
        $result = $writer->u64(0x0102_0304_0506_0708);
        static::assertSame("\x01\x02\x03\x04\x05\x06\x07\x08", $result->toString());
    }

    public function testI8(): void
    {
        $writer = new Writer();
        $result = $writer->i8(-1)->i8(127)->i8(-128);
        static::assertSame("\xFF\x7F\x80", $result->toString());
    }

    public function testI16(): void
    {
        $writer = new Writer();
        $result = $writer->i16(-1, Endianness::Big);
        static::assertSame("\xFF\xFF", $result->toString());
    }

    public function testI32(): void
    {
        $writer = new Writer();
        $result = $writer->i32(-1, Endianness::Big);
        static::assertSame("\xFF\xFF\xFF\xFF", $result->toString());
    }

    public function testI64(): void
    {
        $writer = new Writer();
        $result = $writer->i64(-1, Endianness::Big);
        static::assertSame("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF", $result->toString());
    }

    public function testF32(): void
    {
        $writer = new Writer();
        $result = $writer->f32(1.0, Endianness::Big);
        // IEEE 754: 1.0f = 0x3F800000
        static::assertSame("\x3F\x80\x00\x00", $result->toString());
    }

    public function testF64(): void
    {
        $writer = new Writer();
        $result = $writer->f64(1.0, Endianness::Big);
        // IEEE 754: 1.0 = 0x3FF0000000000000
        static::assertSame("\x3F\xF0\x00\x00\x00\x00\x00\x00", $result->toString());
    }

    public function testBytes(): void
    {
        $writer = new Writer();
        $result = $writer->bytes("\x01\x02\x03");
        static::assertSame("\x01\x02\x03", $result->toString());
    }

    public function testChaining(): void
    {
        $result = new Writer()
            ->u8(0x01)
            ->u16(0x0203, Endianness::Big)
            ->u32(0x0405_0607, Endianness::Big)
            ->toString();

        static::assertSame("\x01\x02\x03\x04\x05\x06\x07", $result);
    }

    public function testDefaultEndiannessPropagates(): void
    {
        $writer = new Writer(endianness: Endianness::Little);
        $result = $writer->u16(0x0102)->u32(0x0304_0506);
        // Both should be little-endian since no per-call override
        static::assertSame("\x02\x01\x06\x05\x04\x03", $result->toString());
    }

    public function testStringable(): void
    {
        $writer = new Writer()->u8(42);
        static::assertSame($writer->toString(), (string) $writer);
    }

    public function testInitialBytes(): void
    {
        $writer = new Writer("\x01\x02");
        $result = $writer->u8(0x03);
        static::assertSame("\x01\x02\x03", $result->toString());
    }

    public function testComplexMessage(): void
    {
        // Simulate a simple binary protocol: version(u8) + type(u16) + payload_length(u32) + payload
        $result = new Writer()
            ->u8(1) // version
            ->u16(0x0042) // message type
            ->u32(5) // payload length
            ->bytes('Hello') // payload
            ->toString();

        static::assertSame("\x01\x00\x42\x00\x00\x00\x05Hello", $result);
    }

    public function testU8PrefixedBytes(): void
    {
        $result = new Writer()
            ->u8PrefixedBytes('Hi')
            ->toString();
        // u8(2) + "Hi"
        static::assertSame("\x02Hi", $result);
    }

    public function testU16PrefixedBytes(): void
    {
        $result = new Writer()
            ->u16PrefixedBytes('Hi', Endianness::Big)
            ->toString();
        // u16(2, Big) + "Hi"
        static::assertSame("\x00\x02Hi", $result);
    }

    public function testU16PrefixedBytesLittleEndian(): void
    {
        $result = new Writer(endianness: Endianness::Little)
            ->u16PrefixedBytes('Hi')
            ->toString();
        // u16(2, Little) + "Hi"
        static::assertSame("\x02\x00Hi", $result);
    }

    public function testU32PrefixedBytes(): void
    {
        $result = new Writer()
            ->u32PrefixedBytes('Hello')
            ->toString();
        // u32(5) + "Hello"
        static::assertSame("\x00\x00\x00\x05Hello", $result);
    }

    public function testU64PrefixedBytes(): void
    {
        $result = new Writer()
            ->u64PrefixedBytes('PSL')
            ->toString();
        // u64(3) + "PSL"
        static::assertSame("\x00\x00\x00\x00\x00\x00\x00\x03PSL", $result);
    }

    public function testPrefixedBytesEmptyString(): void
    {
        $result = new Writer()
            ->u32PrefixedBytes('')
            ->toString();
        // u32(0) + ""
        static::assertSame("\x00\x00\x00\x00", $result);
    }

    public function testPrefixedBytesChaining(): void
    {
        $result = new Writer()
            ->u8(1) // version
            ->u32PrefixedBytes('Hello') // length-prefixed payload
            ->u32PrefixedBytes('World') // another payload
            ->toString();

        static::assertSame("\x01\x00\x00\x00\x05Hello\x00\x00\x00\x05World", $result);
    }

    public function testComplexMessageWithPrefixedBytes(): void
    {
        // Same as testComplexMessage but using u32PrefixedBytes
        $result = new Writer()
            ->u8(1) // version
            ->u16(0x0042) // message type
            ->u32PrefixedBytes('Hello') // length-prefixed payload
            ->toString();

        static::assertSame("\x01\x00\x42\x00\x00\x00\x05Hello", $result);
    }
}
