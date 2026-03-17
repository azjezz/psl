<?php

declare(strict_types=1);

namespace Psl\Binary\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Binary;
use Psl\Binary\BufferedReaderInterface;
use Psl\Binary\Endianness;
use Psl\Binary\Exception;
use Psl\Binary\Reader;
use Psl\Binary\ReaderInterface;
use Psl\Binary\Writer;

use const PHP_INT_MIN;

final class ReaderTest extends TestCase
{
    public function testImplementsBufferedReaderInterface(): void
    {
        $reader = new Reader('');
        static::assertInstanceOf(BufferedReaderInterface::class, $reader);
        static::assertInstanceOf(ReaderInterface::class, $reader);
    }

    public function testEmptyReader(): void
    {
        $reader = new Reader('');
        static::assertSame(0, $reader->cursor());
        static::assertSame(0, $reader->length());
        static::assertSame(0, $reader->remaining());
        static::assertTrue($reader->isConsumed());
    }

    public function testCursorAdvancement(): void
    {
        $reader = new Reader("\x01\x00\x02\x00\x03");
        static::assertSame(0, $reader->cursor());
        static::assertSame(5, $reader->length());
        static::assertSame(5, $reader->remaining());
        static::assertFalse($reader->isConsumed());

        $reader->u8();
        static::assertSame(1, $reader->cursor());
        static::assertSame(4, $reader->remaining());

        $reader->u16(Endianness::Big);
        static::assertSame(3, $reader->cursor());
        static::assertSame(2, $reader->remaining());

        $reader->u8();
        static::assertSame(4, $reader->cursor());
        static::assertSame(1, $reader->remaining());
        static::assertFalse($reader->isConsumed());

        $reader->u8();
        static::assertSame(5, $reader->cursor());
        static::assertSame(0, $reader->remaining());
        static::assertTrue($reader->isConsumed());
    }

    public function testU8(): void
    {
        $reader = new Reader("\x00\xFF\x2A");
        static::assertSame(0, $reader->u8());
        static::assertSame(255, $reader->u8());
        static::assertSame(42, $reader->u8());
    }

    public function testU16Big(): void
    {
        $reader = new Reader("\x01\x02", Endianness::Big);
        static::assertSame(0x0102, $reader->u16());
    }

    public function testU16Little(): void
    {
        $reader = new Reader("\x02\x01", Endianness::Little);
        static::assertSame(0x0102, $reader->u16());
    }

    public function testU16PerCallEndianness(): void
    {
        $reader = new Reader("\x01\x02", Endianness::Big);
        static::assertSame(0x0201, $reader->u16(Endianness::Little));
    }

    public function testU32PerCallEndianness(): void
    {
        $reader = new Reader("\x01\x02\x03\x04", Endianness::Big);
        static::assertSame(0x0403_0201, $reader->u32(Endianness::Little));
    }

    public function testU64PerCallEndianness(): void
    {
        $reader = new Reader("\x01\x02\x03\x04\x05\x06\x07\x08", Endianness::Big);
        static::assertSame(0x0807_0605_0403_0201, $reader->u64(Endianness::Little));
    }

    public function testI16PerCallEndianness(): void
    {
        $reader = new Reader("\x01\x00", Endianness::Big);
        static::assertSame(1, $reader->i16(Endianness::Little));
    }

    public function testI32PerCallEndianness(): void
    {
        $reader = new Reader("\x01\x00\x00\x00", Endianness::Big);
        static::assertSame(1, $reader->i32(Endianness::Little));
    }

    public function testI64PerCallEndianness(): void
    {
        $reader = new Reader("\x01\x00\x00\x00\x00\x00\x00\x00", Endianness::Big);
        static::assertSame(1, $reader->i64(Endianness::Little));
    }

    public function testF32PerCallEndianness(): void
    {
        $bytes = Binary\encode_f32(1.0, Endianness::Little);
        $reader = new Reader($bytes, Endianness::Big);
        static::assertSame(1.0, $reader->f32(Endianness::Little));
    }

    public function testF64PerCallEndianness(): void
    {
        $bytes = Binary\encode_f64(1.0, Endianness::Little);
        $reader = new Reader($bytes, Endianness::Big);
        static::assertSame(1.0, $reader->f64(Endianness::Little));
    }

    public function testU32(): void
    {
        $reader = new Reader("\x01\x02\x03\x04", Endianness::Big);
        static::assertSame(0x0102_0304, $reader->u32());
    }

    public function testU64(): void
    {
        $reader = new Reader("\x01\x02\x03\x04\x05\x06\x07\x08", Endianness::Big);
        static::assertSame(0x0102_0304_0506_0708, $reader->u64());
    }

    public function testI8(): void
    {
        $reader = new Reader("\xFF\x7F\x80");
        static::assertSame(-1, $reader->i8());
        static::assertSame(127, $reader->i8());
        static::assertSame(-128, $reader->i8());
    }

    public function testI16(): void
    {
        $reader = new Reader("\xFF\xFF", Endianness::Big);
        static::assertSame(-1, $reader->i16());
    }

    public function testI32(): void
    {
        $reader = new Reader("\xFF\xFF\xFF\xFF", Endianness::Big);
        static::assertSame(-1, $reader->i32());
    }

    public function testI64(): void
    {
        $reader = new Reader("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF", Endianness::Big);
        static::assertSame(-1, $reader->i64());
    }

    public function testF32(): void
    {
        $reader = new Reader("\x3F\x80\x00\x00", Endianness::Big);
        static::assertSame(1.0, $reader->f32());
    }

    public function testF64(): void
    {
        $reader = new Reader("\x3F\xF0\x00\x00\x00\x00\x00\x00", Endianness::Big);
        static::assertSame(1.0, $reader->f64());
    }

    public function testBytes(): void
    {
        $reader = new Reader('Hello');
        static::assertSame('Hel', $reader->bytes(3));
        static::assertSame('lo', $reader->bytes(2));
    }

    public function testUnderflowOnU8(): void
    {
        $reader = new Reader('');
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected at least 1 bytes, got 0.');
        $reader->u8();
    }

    public function testUnderflowOnU16(): void
    {
        $reader = new Reader("\x00");
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected at least 2 bytes, got 1.');
        $reader->u16();
    }

    public function testUnderflowOnU32(): void
    {
        $reader = new Reader("\x00\x00\x00");
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected at least 4 bytes, got 3.');
        $reader->u32();
    }

    public function testUnderflowOnU64(): void
    {
        $reader = new Reader("\x00\x00\x00\x00\x00\x00\x00");
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected at least 8 bytes, got 7.');
        $reader->u64();
    }

    public function testUnderflowOnI8(): void
    {
        $reader = new Reader('');
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected at least 1 bytes, got 0.');
        $reader->i8();
    }

    public function testUnderflowOnI16(): void
    {
        $reader = new Reader("\x00");
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected at least 2 bytes, got 1.');
        $reader->i16();
    }

    public function testUnderflowOnI32(): void
    {
        $reader = new Reader("\x00\x00\x00");
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected at least 4 bytes, got 3.');
        $reader->i32();
    }

    public function testUnderflowOnI64(): void
    {
        $reader = new Reader("\x00\x00\x00\x00\x00\x00\x00");
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected at least 8 bytes, got 7.');
        $reader->i64();
    }

    public function testUnderflowOnF32(): void
    {
        $reader = new Reader("\x00\x00\x00");
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected at least 4 bytes, got 3.');
        $reader->f32();
    }

    public function testUnderflowOnF64(): void
    {
        $reader = new Reader("\x00\x00\x00\x00\x00\x00\x00");
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected at least 8 bytes, got 7.');
        $reader->f64();
    }

    public function testUnderflowOnBytes(): void
    {
        $reader = new Reader("\x00\x00");
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected at least 3 bytes, got 2.');
        $reader->bytes(3);
    }

    public function testDefaultEndianness(): void
    {
        // Default is Big
        $reader = new Reader("\x01\x02");
        static::assertSame(0x0102, $reader->u16());
    }

    public function testWriterReaderRoundTrip(): void
    {
        $data = new Writer()
            ->u8(1)
            ->u16(0x0203)
            ->u32(0x0405_0607)
            ->u64(0x0809_0A0B_0C0D_0E0F)
            ->i8(-1)
            ->i16(-256)
            ->i32(-65_536)
            ->i64(PHP_INT_MIN)
            ->f32(1.0)
            ->f64(3.141_592_653_589_793)
            ->bytes('PSL')
            ->toString();

        $reader = new Reader($data);
        static::assertSame(1, $reader->u8());
        static::assertSame(0x0203, $reader->u16());
        static::assertSame(0x0405_0607, $reader->u32());
        static::assertSame(0x0809_0A0B_0C0D_0E0F, $reader->u64());
        static::assertSame(-1, $reader->i8());
        static::assertSame(-256, $reader->i16());
        static::assertSame(-65_536, $reader->i32());
        static::assertSame(PHP_INT_MIN, $reader->i64());
        static::assertSame(1.0, $reader->f32());
        static::assertSame(3.141_592_653_589_793, $reader->f64());
        static::assertSame('PSL', $reader->bytes(3));
        static::assertTrue($reader->isConsumed());
    }

    public function testComplexProtocolRoundTrip(): void
    {
        // Write a simple protocol message
        $message = new Writer(endianness: Endianness::Little)
            ->u8(2) // version
            ->u16(0x0042) // type
            ->u32(3) // payload length
            ->bytes('PSL') // payload
            ->toString();

        $reader = new Reader($message, Endianness::Little);
        static::assertSame(2, $reader->u8());
        static::assertSame(0x0042, $reader->u16());
        $payloadLen = $reader->u32();
        static::assertSame(3, $payloadLen);
        static::assertSame('PSL', $reader->bytes($payloadLen));
        static::assertTrue($reader->isConsumed());
    }

    public function testSequentialReadsExhaustBuffer(): void
    {
        $reader = new Reader("\x01\x02\x03\x04");
        $reader->u8();
        $reader->u8();
        $reader->u8();
        $reader->u8();
        static::assertTrue($reader->isConsumed());

        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected at least 1 bytes, got 0.');
        $reader->u8();
    }

    public function testSkip(): void
    {
        $reader = new Reader("\x01\x02\x03\x04\x05");
        $reader->skip(2);
        static::assertSame(2, $reader->cursor());
        static::assertSame(3, $reader->remaining());
        static::assertSame(3, $reader->u8());
    }

    public function testSkipZero(): void
    {
        $reader = new Reader("\x01\x02");
        $reader->skip(0);
        static::assertSame(0, $reader->cursor());
    }

    public function testSkipAll(): void
    {
        $reader = new Reader("\x01\x02\x03");
        $reader->skip(3);
        static::assertTrue($reader->isConsumed());
    }

    public function testSkipUnderflow(): void
    {
        $reader = new Reader("\x01\x02");
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected at least 5 bytes, got 2.');
        $reader->skip(5);
    }

    public function testU8PrefixedBytes(): void
    {
        $data = new Writer()
            ->u8PrefixedBytes('Hi')
            ->toString();
        $reader = new Reader($data);
        static::assertSame('Hi', $reader->u8PrefixedBytes());
        static::assertTrue($reader->isConsumed());
    }

    public function testU16PrefixedBytes(): void
    {
        $data = new Writer()
            ->u16PrefixedBytes('Hello', Endianness::Big)
            ->toString();
        $reader = new Reader($data, Endianness::Big);
        static::assertSame('Hello', $reader->u16PrefixedBytes());
        static::assertTrue($reader->isConsumed());
    }

    public function testU32PrefixedBytes(): void
    {
        $data = new Writer()
            ->u32PrefixedBytes('PSL')
            ->toString();
        $reader = new Reader($data);
        static::assertSame('PSL', $reader->u32PrefixedBytes());
        static::assertTrue($reader->isConsumed());
    }

    public function testU64PrefixedBytes(): void
    {
        $data = new Writer()
            ->u64PrefixedBytes('World')
            ->toString();
        $reader = new Reader($data);
        static::assertSame('World', $reader->u64PrefixedBytes());
        static::assertTrue($reader->isConsumed());
    }

    public function testPrefixedBytesEmpty(): void
    {
        $data = new Writer()
            ->u32PrefixedBytes('')
            ->toString();
        $reader = new Reader($data);
        static::assertSame('', $reader->u32PrefixedBytes());
        static::assertTrue($reader->isConsumed());
    }

    public function testPrefixedBytesWithEndianness(): void
    {
        $data = new Writer(endianness: Endianness::Little)
            ->u16PrefixedBytes('AB')
            ->toString();
        $reader = new Reader($data, Endianness::Little);
        static::assertSame('AB', $reader->u16PrefixedBytes());
        static::assertTrue($reader->isConsumed());
    }

    public function testSkipThenPrefixedBytes(): void
    {
        // version(u8) + padding(3 bytes) + u32PrefixedBytes payload
        $data = new Writer()
            ->u8(1)
            ->bytes("\x00\x00\x00") // padding
            ->u32PrefixedBytes('Hello')
            ->toString();

        $reader = new Reader($data);
        static::assertSame(1, $reader->u8());
        $reader->skip(3); // skip padding
        static::assertSame('Hello', $reader->u32PrefixedBytes());
        static::assertTrue($reader->isConsumed());
    }

    public function testMultiplePrefixedBytesRoundTrip(): void
    {
        $data = new Writer()
            ->u8PrefixedBytes('A')
            ->u16PrefixedBytes('BB')
            ->u32PrefixedBytes('CCC')
            ->u64PrefixedBytes('DDDD')
            ->toString();

        $reader = new Reader($data);
        static::assertSame('A', $reader->u8PrefixedBytes());
        static::assertSame('BB', $reader->u16PrefixedBytes());
        static::assertSame('CCC', $reader->u32PrefixedBytes());
        static::assertSame('DDDD', $reader->u64PrefixedBytes());
        static::assertTrue($reader->isConsumed());
    }
}
