<?php

declare(strict_types=1);

namespace Psl\Binary\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Binary;
use Psl\Binary\Endianness;
use Psl\Binary\Exception;
use Psl\Binary\HandleReader;
use Psl\Binary\HandleWriter;
use Psl\Binary\ReaderInterface;
use Psl\Binary\Writer;
use Psl\IO;
use Psl\IO\MemoryHandle;

use function strlen;
use function substr;

use const PHP_INT_MIN;

final class HandleReaderTest extends TestCase
{
    public function testImplementsReaderInterface(): void
    {
        $handle = new MemoryHandle('');
        $reader = new HandleReader($handle);
        static::assertInstanceOf(ReaderInterface::class, $reader);
    }

    public function testIsConsumedOnEmptyHandle(): void
    {
        $handle = new MemoryHandle('');
        // Need to attempt a read to trigger EOF detection
        $handle->tryRead();
        $reader = new HandleReader($handle);
        static::assertTrue($reader->isConsumed());
    }

    public function testU8(): void
    {
        $handle = new MemoryHandle("\x00\xFF\x2A");
        $reader = new HandleReader($handle);
        static::assertSame(0, $reader->u8());
        static::assertSame(255, $reader->u8());
        static::assertSame(42, $reader->u8());
    }

    public function testU16Big(): void
    {
        $handle = new MemoryHandle("\x01\x02");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(0x0102, $reader->u16());
    }

    public function testU16Little(): void
    {
        $handle = new MemoryHandle("\x02\x01");
        $reader = new HandleReader($handle, Endianness::Little);
        static::assertSame(0x0102, $reader->u16());
    }

    public function testU16PerCallEndianness(): void
    {
        $handle = new MemoryHandle("\x01\x02");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(0x0201, $reader->u16(Endianness::Little));
    }

    public function testU32PerCallEndianness(): void
    {
        $handle = new MemoryHandle("\x01\x02\x03\x04");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(0x0403_0201, $reader->u32(Endianness::Little));
    }

    public function testU64PerCallEndianness(): void
    {
        $handle = new MemoryHandle("\x01\x02\x03\x04\x05\x06\x07\x08");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(0x0807_0605_0403_0201, $reader->u64(Endianness::Little));
    }

    public function testI16PerCallEndianness(): void
    {
        $handle = new MemoryHandle("\x01\x00");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(1, $reader->i16(Endianness::Little));
    }

    public function testI32PerCallEndianness(): void
    {
        $handle = new MemoryHandle("\x01\x00\x00\x00");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(1, $reader->i32(Endianness::Little));
    }

    public function testI64PerCallEndianness(): void
    {
        $handle = new MemoryHandle("\x01\x00\x00\x00\x00\x00\x00\x00");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(1, $reader->i64(Endianness::Little));
    }

    public function testF32PerCallEndianness(): void
    {
        $bytes = Binary\encode_f32(1.0, Endianness::Little);
        $handle = new MemoryHandle($bytes);
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(1.0, $reader->f32(Endianness::Little));
    }

    public function testF64PerCallEndianness(): void
    {
        $bytes = Binary\encode_f64(1.0, Endianness::Little);
        $handle = new MemoryHandle($bytes);
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(1.0, $reader->f64(Endianness::Little));
    }

    public function testU32(): void
    {
        $handle = new MemoryHandle("\x01\x02\x03\x04");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(0x0102_0304, $reader->u32());
    }

    public function testU64(): void
    {
        $handle = new MemoryHandle("\x01\x02\x03\x04\x05\x06\x07\x08");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(0x0102_0304_0506_0708, $reader->u64());
    }

    public function testI8(): void
    {
        $handle = new MemoryHandle("\xFF\x7F\x80");
        $reader = new HandleReader($handle);
        static::assertSame(-1, $reader->i8());
        static::assertSame(127, $reader->i8());
        static::assertSame(-128, $reader->i8());
    }

    public function testI16(): void
    {
        $handle = new MemoryHandle("\xFF\xFF");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(-1, $reader->i16());
    }

    public function testI32(): void
    {
        $handle = new MemoryHandle("\xFF\xFF\xFF\xFF");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(-1, $reader->i32());
    }

    public function testI64(): void
    {
        $handle = new MemoryHandle("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(-1, $reader->i64());
    }

    public function testF32(): void
    {
        $handle = new MemoryHandle("\x3F\x80\x00\x00");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(1.0, $reader->f32());
    }

    public function testF64(): void
    {
        $handle = new MemoryHandle("\x3F\xF0\x00\x00\x00\x00\x00\x00");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame(1.0, $reader->f64());
    }

    public function testBytes(): void
    {
        $handle = new MemoryHandle('Hello');
        $reader = new HandleReader($handle);
        static::assertSame('Hel', $reader->bytes(3));
        static::assertSame('lo', $reader->bytes(2));
    }

    public function testUnderflowOnU8(): void
    {
        $handle = new MemoryHandle('');
        $reader = new HandleReader($handle);
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected to read 1 bytes, but the handle reached end of data.');
        $reader->u8();
    }

    public function testUnderflowOnU16(): void
    {
        $handle = new MemoryHandle("\x00");
        $reader = new HandleReader($handle);
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected to read 2 bytes, but the handle reached end of data.');
        $reader->u16();
    }

    public function testUnderflowOnU32(): void
    {
        $handle = new MemoryHandle("\x00\x00\x00");
        $reader = new HandleReader($handle);
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected to read 4 bytes, but the handle reached end of data.');
        $reader->u32();
    }

    public function testUnderflowOnBytes(): void
    {
        $handle = new MemoryHandle("\x00\x00");
        $reader = new HandleReader($handle);
        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected to read 3 bytes, but the handle reached end of data.');
        $reader->bytes(3);
    }

    public function testIsConsumedAfterReadingAll(): void
    {
        $handle = new MemoryHandle("\x01\x02");
        $reader = new HandleReader($handle);
        static::assertFalse($reader->isConsumed());
        $reader->u8();
        $reader->u8();
        // MemoryHandle only sets EOF after a read attempt past the end
        // Trigger it by attempting to read
        $handle->tryRead();
        static::assertTrue($reader->isConsumed());
    }

    public function testHandleWriterToHandleReaderRoundTrip(): void
    {
        $handle = new MemoryHandle();

        $writer = new HandleWriter($handle);
        $writer
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
            ->bytes('PSL');

        // Seek back to start to read
        $handle->seek(0);

        $reader = new HandleReader($handle);
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
        // MemoryHandle only sets EOF after a read attempt past the end
        $handle->tryRead();
        static::assertTrue($reader->isConsumed());
    }

    public function testBufferedWriterToHandleReaderRoundTrip(): void
    {
        // Write with buffered Writer, read with HandleReader
        $data = new Writer()
            ->u8(1)
            ->u16(0x0042)
            ->u32(3)
            ->bytes('PSL')
            ->toString();

        $handle = new MemoryHandle($data);
        $reader = new HandleReader($handle);

        static::assertSame(1, $reader->u8());
        static::assertSame(0x0042, $reader->u16());
        $len = $reader->u32();
        static::assertSame(3, $len);
        static::assertSame('PSL', $reader->bytes($len));
        // MemoryHandle only sets EOF after a read attempt past the end
        $handle->tryRead();
        static::assertTrue($reader->isConsumed());
    }

    public function testSkipUsesSeek(): void
    {
        // MemoryHandle implements SeekHandleInterface, so skip should use seek
        $handle = new MemoryHandle("\x01\x02\x03\x04\x05");
        static::assertInstanceOf(IO\SeekHandleInterface::class, $handle);

        $reader = new HandleReader($handle);
        $reader->skip(2);
        static::assertSame(3, $reader->u8());
    }

    public function testSkipZero(): void
    {
        $handle = new MemoryHandle("\x01\x02");
        $reader = new HandleReader($handle);
        $reader->skip(0);
        static::assertSame(1, $reader->u8());
    }

    public function testSkipAll(): void
    {
        $handle = new MemoryHandle("\x01\x02\x03");
        $reader = new HandleReader($handle);
        $reader->skip(3);
        $handle->tryRead();
        static::assertTrue($reader->isConsumed());
    }

    public function testSkipUnderflowOnSeekableHandle(): void
    {
        // MemoryHandle is seekable, so skip uses seek; seeking past the
        // end is allowed (like fseek), but the next read will underflow.
        $handle = new MemoryHandle("\x01\x02");
        $reader = new HandleReader($handle);
        $reader->skip(100);

        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected to read 1 bytes, but the handle reached end of data.');
        $reader->u8();
    }

    public function testU8PrefixedBytes(): void
    {
        $handle = new MemoryHandle("\x02Hi");
        $reader = new HandleReader($handle);
        static::assertSame('Hi', $reader->u8PrefixedBytes());
    }

    public function testU16PrefixedBytes(): void
    {
        $handle = new MemoryHandle("\x00\x05Hello");
        $reader = new HandleReader($handle, Endianness::Big);
        static::assertSame('Hello', $reader->u16PrefixedBytes());
    }

    public function testU32PrefixedBytes(): void
    {
        $handle = new MemoryHandle("\x00\x00\x00\x03PSL");
        $reader = new HandleReader($handle);
        static::assertSame('PSL', $reader->u32PrefixedBytes());
    }

    public function testU64PrefixedBytes(): void
    {
        $handle = new MemoryHandle("\x00\x00\x00\x00\x00\x00\x00\x05World");
        $reader = new HandleReader($handle);
        static::assertSame('World', $reader->u64PrefixedBytes());
    }

    public function testPrefixedBytesRoundTripWithHandleWriter(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->u8PrefixedBytes('A')->u16PrefixedBytes('BB')->u32PrefixedBytes('CCC')->u64PrefixedBytes('DDDD');

        $handle->seek(0);
        $reader = new HandleReader($handle);
        static::assertSame('A', $reader->u8PrefixedBytes());
        static::assertSame('BB', $reader->u16PrefixedBytes());
        static::assertSame('CCC', $reader->u32PrefixedBytes());
        static::assertSame('DDDD', $reader->u64PrefixedBytes());
        $handle->tryRead();
        static::assertTrue($reader->isConsumed());
    }

    public function testSkipThenPrefixedBytes(): void
    {
        $handle = new MemoryHandle();
        $writer = new HandleWriter($handle);
        $writer->u8(1)->bytes("\x00\x00\x00")->u32PrefixedBytes('Hello');

        $handle->seek(0);
        $reader = new HandleReader($handle);
        static::assertSame(1, $reader->u8());
        $reader->skip(3); // skip padding
        static::assertSame('Hello', $reader->u32PrefixedBytes());
    }

    public function testBytesZeroLength(): void
    {
        $handle = new MemoryHandle("\x01\x02");
        $reader = new HandleReader($handle);
        static::assertSame('', $reader->bytes(0));
        // Verify the cursor hasn't advanced
        static::assertSame(1, $reader->u8());
    }

    public function testSkipUnderflowWhenSeekThrows(): void
    {
        $handle = new class() implements IO\ReadHandleInterface, IO\SeekHandleInterface {
            use IO\ReadHandleConvenienceMethodsTrait;

            public function reachedEndOfDataSource(): bool
            {
                return true;
            }

            public function tryRead(null|int $maxBytes = null): string
            {
                return '';
            }

            public function read(
                null|int $maxBytes = null,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): string {
                return '';
            }

            public function seek(int $offset): void
            {
                throw new IO\Exception\RuntimeException('Seek failed');
            }

            public function tell(): int
            {
                throw new IO\Exception\RuntimeException('Tell failed');
            }
        };

        $reader = new HandleReader($handle);

        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Failed to skip 5 bytes: the handle reached end of data.');
        $reader->skip(5);
    }

    public function testSkipOnNonSeekableHandle(): void
    {
        $handle = new class("\x01\x02\x03\x04\x05") implements IO\ReadHandleInterface {
            use IO\ReadHandleConvenienceMethodsTrait;

            private int $cursor = 0;

            public function __construct(
                private readonly string $data,
            ) {}

            public function reachedEndOfDataSource(): bool
            {
                return $this->cursor >= strlen($this->data);
            }

            public function tryRead(null|int $maxBytes = null): string
            {
                if ($this->cursor >= strlen($this->data)) {
                    return '';
                }

                $max = $maxBytes ?? (strlen($this->data) - $this->cursor);
                $result = substr($this->data, $this->cursor, $max);
                $this->cursor += strlen($result);

                return $result;
            }

            public function read(
                null|int $maxBytes = null,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): string {
                return $this->tryRead($maxBytes);
            }
        };

        static::assertNotInstanceOf(IO\SeekHandleInterface::class, $handle);

        $reader = new HandleReader($handle);
        // skip uses read-and-discard on non-seekable handles
        $reader->skip(2);
        static::assertSame(3, $reader->u8());
    }

    public function testSkipUnderflowOnNonSeekableHandle(): void
    {
        $handle = new class("\x01\x02") implements IO\ReadHandleInterface {
            use IO\ReadHandleConvenienceMethodsTrait;

            private int $cursor = 0;

            public function __construct(
                private readonly string $data,
            ) {}

            public function reachedEndOfDataSource(): bool
            {
                return $this->cursor >= strlen($this->data);
            }

            public function tryRead(null|int $maxBytes = null): string
            {
                if ($this->cursor >= strlen($this->data)) {
                    return '';
                }

                $max = $maxBytes ?? (strlen($this->data) - $this->cursor);
                $result = substr($this->data, $this->cursor, $max);
                $this->cursor += strlen($result);

                return $result;
            }

            public function read(
                null|int $maxBytes = null,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): string {
                return $this->tryRead($maxBytes);
            }
        };

        $reader = new HandleReader($handle);

        $this->expectException(Exception\UnderflowException::class);
        $this->expectExceptionMessage('Expected to read 10 bytes, but the handle reached end of data.');
        $reader->skip(10);
    }
}
