<?php

declare(strict_types=1);

namespace Psl\Binary\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Binary\Endianness;
use Psl\Binary\HandleWriter;
use Psl\Binary\WriterInterface;
use Psl\IO\MemoryHandle;

final class HandleWriterTest extends TestCase
{
    public function testImplementsWriterInterface(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        static::assertInstanceOf(WriterInterface::class, $writer);
    }

    public function testU8(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->u8(0)->u8(255);
        static::assertSame("\x00\xFF", $handle->getBuffer());
    }

    public function testU16BigEndian(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle, Endianness::Big);
        $writer->u16(0x0102);
        static::assertSame("\x01\x02", $handle->getBuffer());
    }

    public function testU16LittleEndian(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle, Endianness::Little);
        $writer->u16(0x0102);
        static::assertSame("\x02\x01", $handle->getBuffer());
    }

    public function testU16PerCallEndianness(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle, Endianness::Big);
        $writer->u16(0x0102, Endianness::Little);
        static::assertSame("\x02\x01", $handle->getBuffer());
    }

    public function testU32PerCallEndianness(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle, Endianness::Big);
        $writer->u32(0x0102_0304, Endianness::Little);
        static::assertSame("\x04\x03\x02\x01", $handle->getBuffer());
    }

    public function testU64PerCallEndianness(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle, Endianness::Big);
        $writer->u64(0x0102_0304_0506_0708, Endianness::Little);
        static::assertSame("\x08\x07\x06\x05\x04\x03\x02\x01", $handle->getBuffer());
    }

    public function testI16PerCallEndianness(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle, Endianness::Big);
        $writer->i16(256, Endianness::Little);
        static::assertSame("\x00\x01", $handle->getBuffer());
    }

    public function testI32PerCallEndianness(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle, Endianness::Big);
        $writer->i32(1, Endianness::Little);
        static::assertSame("\x01\x00\x00\x00", $handle->getBuffer());
    }

    public function testI64PerCallEndianness(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle, Endianness::Big);
        $writer->i64(1, Endianness::Little);
        static::assertSame("\x01\x00\x00\x00\x00\x00\x00\x00", $handle->getBuffer());
    }

    public function testF32PerCallEndianness(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle, Endianness::Big);
        $writer->f32(1.0, Endianness::Little);
        static::assertSame("\x00\x00\x80\x3F", $handle->getBuffer());
    }

    public function testF64PerCallEndianness(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle, Endianness::Big);
        $writer->f64(1.0, Endianness::Little);
        static::assertSame("\x00\x00\x00\x00\x00\x00\xF0\x3F", $handle->getBuffer());
    }

    public function testU32(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->u32(0x0102_0304);
        static::assertSame("\x01\x02\x03\x04", $handle->getBuffer());
    }

    public function testU64(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->u64(0x0102_0304_0506_0708);
        static::assertSame("\x01\x02\x03\x04\x05\x06\x07\x08", $handle->getBuffer());
    }

    public function testI8(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->i8(-1)->i8(127)->i8(-128);
        static::assertSame("\xFF\x7F\x80", $handle->getBuffer());
    }

    public function testI16(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->i16(-1, Endianness::Big);
        static::assertSame("\xFF\xFF", $handle->getBuffer());
    }

    public function testI32(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->i32(-1, Endianness::Big);
        static::assertSame("\xFF\xFF\xFF\xFF", $handle->getBuffer());
    }

    public function testI64(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->i64(-1, Endianness::Big);
        static::assertSame("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF", $handle->getBuffer());
    }

    public function testF32(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->f32(1.0, Endianness::Big);
        static::assertSame("\x3F\x80\x00\x00", $handle->getBuffer());
    }

    public function testF64(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->f64(1.0, Endianness::Big);
        static::assertSame("\x3F\xF0\x00\x00\x00\x00\x00\x00", $handle->getBuffer());
    }

    public function testBytes(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->bytes("\x01\x02\x03");
        static::assertSame("\x01\x02\x03", $handle->getBuffer());
    }

    public function testChaining(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $result = $writer->u8(0x01)->u16(0x0203, Endianness::Big)->u32(0x0405_0607, Endianness::Big);

        static::assertSame($writer, $result);
        static::assertSame("\x01\x02\x03\x04\x05\x06\x07", $handle->getBuffer());
    }

    public function testReturnsSameInstance(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $result = $writer->u8(42);
        static::assertSame($writer, $result);
    }

    public function testComplexMessage(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->u8(1)->u16(0x0042)->u32(5)->bytes('Hello');

        static::assertSame("\x01\x00\x42\x00\x00\x00\x05Hello", $handle->getBuffer());
    }

    public function testU8PrefixedBytes(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->u8PrefixedBytes('Hi');
        static::assertSame("\x02Hi", $handle->getBuffer());
    }

    public function testU16PrefixedBytes(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle, Endianness::Big);
        $writer->u16PrefixedBytes('Hi');
        static::assertSame("\x00\x02Hi", $handle->getBuffer());
    }

    public function testU32PrefixedBytes(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->u32PrefixedBytes('Hello');
        static::assertSame("\x00\x00\x00\x05Hello", $handle->getBuffer());
    }

    public function testU64PrefixedBytes(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->u64PrefixedBytes('PSL');
        static::assertSame("\x00\x00\x00\x00\x00\x00\x00\x03PSL", $handle->getBuffer());
    }

    public function testPrefixedBytesChaining(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $result = $writer->u8(1)->u32PrefixedBytes('Hello');

        static::assertSame($writer, $result);
        static::assertSame("\x01\x00\x00\x00\x05Hello", $handle->getBuffer());
    }

    public function testComplexMessageWithPrefixedBytes(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->u8(1)->u16(0x0042)->u32PrefixedBytes('Hello');

        static::assertSame("\x01\x00\x42\x00\x00\x00\x05Hello", $handle->getBuffer());
    }
}
