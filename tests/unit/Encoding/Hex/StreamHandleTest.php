<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Encoding\Hex;

use PHPUnit\Framework\TestCase;
use Psl\Encoding\Exception;
use Psl\Encoding\Hex;
use Psl\IO;

final class StreamHandleTest extends TestCase
{
    public function testEncodingReadHandle(): void
    {
        $inner = new IO\MemoryHandle('Hello');
        $handle = new Hex\EncodingReadHandle($inner);

        static::assertSame('48656c6c6f', $handle->readAll());
    }

    public function testDecodingReadHandle(): void
    {
        $inner = new IO\MemoryHandle('48656c6c6f');
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('Hello', $handle->readAll());
    }

    public function testEncodingWriteHandle(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Hex\EncodingWriteHandle($inner);

        $handle->writeAll('Hello');

        $inner->seek(0);
        static::assertSame('48656c6c6f', $inner->readAll());
    }

    public function testDecodingWriteHandle(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Hex\DecodingWriteHandle($inner);

        $handle->writeAll('48656c6c6f');
        $handle->flush();

        $inner->seek(0);
        static::assertSame('Hello', $inner->readAll());
    }

    public function testRoundTripReadHandles(): void
    {
        $original = "\x00\x01\xff\xfe test data";

        $raw = new IO\MemoryHandle($original);
        $encoding = new Hex\EncodingReadHandle($raw);
        $encoded = $encoding->readAll();

        $encodedHandle = new IO\MemoryHandle($encoded);
        $decoding = new Hex\DecodingReadHandle($encodedHandle);

        static::assertSame($original, $decoding->readAll());
    }

    public function testRoundTripWriteHandles(): void
    {
        $original = 'binary data: ' . "\x00\xff";

        $encodedBuffer = new IO\MemoryHandle();
        $encoding = new Hex\EncodingWriteHandle($encodedBuffer);
        $encoding->writeAll($original);

        $decodedBuffer = new IO\MemoryHandle();
        $decoding = new Hex\DecodingWriteHandle($decodedBuffer);
        $encodedBuffer->seek(0);
        $decoding->writeAll($encodedBuffer->readAll());
        $decoding->flush();

        $decodedBuffer->seek(0);
        static::assertSame($original, $decodedBuffer->readAll());
    }

    public function testDecodingReadHandleInvalidHex(): void
    {
        $inner = new IO\MemoryHandle('ZZZZ');
        $handle = new Hex\DecodingReadHandle($inner);

        $this->expectException(Exception\RangeException::class);

        $handle->readAll();
    }

    public function testDecodingWriteHandleOddLength(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Hex\DecodingWriteHandle($inner);

        $handle->writeAll('abc');

        $this->expectException(Exception\RangeException::class);

        $handle->flush();
    }

    public function testEncodingReadHandleEmpty(): void
    {
        $inner = new IO\MemoryHandle('');
        $handle = new Hex\EncodingReadHandle($inner);

        static::assertSame('', $handle->readAll());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testDecodingReadHandleEmpty(): void
    {
        $inner = new IO\MemoryHandle('');
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('', $handle->readAll());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testPartialReads(): void
    {
        $inner = new IO\MemoryHandle('48656c6c6f');
        $handle = new Hex\DecodingReadHandle($inner);

        $result = '';
        while (!$handle->reachedEndOfDataSource()) {
            $chunk = $handle->read(2);
            $result .= $chunk;
        }

        static::assertSame('Hello', $result);
    }

    public function testDecodingWriteHandleChunked(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Hex\DecodingWriteHandle($inner);

        $handle->write('48');
        $handle->write('65');
        $handle->write('6c');
        $handle->flush();

        $inner->seek(0);
        static::assertSame('Hel', $inner->readAll());
    }

    public function testDecodingReadHandleReadByte(): void
    {
        $inner = new IO\MemoryHandle(bin2hex('XYZ'));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('X', $handle->readByte());
        static::assertSame('Y', $handle->readByte());
        static::assertSame('Z', $handle->readByte());
    }

    public function testDecodingReadHandleReadByteOnEmpty(): void
    {
        $inner = new IO\MemoryHandle('');
        $handle = new Hex\DecodingReadHandle($inner);

        $this->expectException(IO\Exception\RuntimeException::class);

        $handle->readByte();
    }

    public function testDecodingReadHandleReadLineLF(): void
    {
        $inner = new IO\MemoryHandle(bin2hex("first\nsecond"));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('first', $handle->readLine());
        static::assertSame('second', $handle->readLine());
        static::assertNull($handle->readLine());
    }

    public function testDecodingReadHandleReadLineCRLF(): void
    {
        $inner = new IO\MemoryHandle(bin2hex("first\r\nsecond"));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('first', $handle->readLine());
        static::assertSame('second', $handle->readLine());
        static::assertNull($handle->readLine());
    }

    public function testDecodingReadHandleReadLineNoNewline(): void
    {
        $inner = new IO\MemoryHandle(bin2hex('single line'));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('single line', $handle->readLine());
        static::assertNull($handle->readLine());
    }

    public function testDecodingReadHandleReadUntil(): void
    {
        $inner = new IO\MemoryHandle(bin2hex('hello|world'));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('hello', $handle->readUntil('|'));
        static::assertNull($handle->readUntil('|'));
    }

    public function testDecodingReadHandleReadUntilNotFound(): void
    {
        $inner = new IO\MemoryHandle(bin2hex('no pipe'));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertNull($handle->readUntil('|'));
    }

    public function testDecodingReadHandleReadUntilBounded(): void
    {
        $inner = new IO\MemoryHandle(bin2hex('abc:rest'));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('abc', $handle->readUntilBounded(':', 10));
    }

    public function testDecodingReadHandleReadUntilBoundedOverflow(): void
    {
        $inner = new IO\MemoryHandle(bin2hex('verylongprefix:end'));
        $handle = new Hex\DecodingReadHandle($inner);

        $this->expectException(IO\Exception\OverflowException::class);

        $handle->readUntilBounded(':end', 3);
    }

    public function testDecodingReadHandleReadUntilBoundedNotFound(): void
    {
        $inner = new IO\MemoryHandle(bin2hex('nothing'));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertNull($handle->readUntilBounded('@', 100));
    }

    public function testEncodingReadHandleReadByte(): void
    {
        $inner = new IO\MemoryHandle('A');
        $handle = new Hex\EncodingReadHandle($inner);

        static::assertSame('4', $handle->readByte());
        static::assertSame('1', $handle->readByte());
    }

    public function testEncodingReadHandleReadLine(): void
    {
        $inner = new IO\MemoryHandle("AB\nCD");
        $handle = new Hex\EncodingReadHandle($inner);

        $line = $handle->readLine();

        static::assertNotNull($line);
    }

    public function testEncodingReadHandleReadUntil(): void
    {
        $inner = new IO\MemoryHandle('Hi');
        $handle = new Hex\EncodingReadHandle($inner);

        $result = $handle->readUntil('69');

        static::assertSame('48', $result);
    }

    public function testDecodingReadHandleReadByteAfterPartialRead(): void
    {
        $inner = new IO\MemoryHandle(bin2hex('ABCDE'));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('AB', $handle->read(2));
        static::assertSame('C', $handle->readByte());
        static::assertSame('DE', $handle->readAll());
    }

    public function testDecodingReadHandleInterleavedMethods(): void
    {
        $inner = new IO\MemoryHandle(bin2hex("line1|line2\nline3"));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('line1', $handle->readUntil('|'));
        static::assertSame('line2', $handle->readLine());
        static::assertSame('l', $handle->readByte());
        static::assertSame('ine3', $handle->readAll());
    }

    public function testDecodingReadHandleTryReadExactBuffer(): void
    {
        $inner = new IO\MemoryHandle(bin2hex('ABCD'));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('ABCD', $handle->tryRead(4));
        static::assertSame('', $handle->tryRead());
    }

    public function testDecodingReadHandleReadAfterEof(): void
    {
        $inner = new IO\MemoryHandle(bin2hex('X'));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('X', $handle->read());
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testDecodingReadHandleReadByteSingle(): void
    {
        $inner = new IO\MemoryHandle(bin2hex('X'));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('X', $handle->readByte());
    }

    public function testDecodingReadHandleReadLineEmpty(): void
    {
        $inner = new IO\MemoryHandle(bin2hex("\n\n"));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('', $handle->readLine());
        static::assertSame('', $handle->readLine());
        static::assertNull($handle->readLine());
    }

    public function testDecodingReadHandleReadLineCRLFStripped(): void
    {
        $inner = new IO\MemoryHandle(bin2hex("a\r\nb"));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('a', $handle->readLine());
        static::assertSame('b', $handle->readLine());
    }

    public function testDecodingReadHandleReadUntilMultiple(): void
    {
        $inner = new IO\MemoryHandle(bin2hex('a|b|c'));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('a', $handle->readUntil('|'));
        static::assertSame('b', $handle->readUntil('|'));
        static::assertNull($handle->readUntil('|'));
    }

    public function testDecodingReadHandleReadUntilBoundedExact(): void
    {
        $inner = new IO\MemoryHandle(bin2hex('abcde:end'));
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('abcde', $handle->readUntilBounded(':end', 5));
    }

    public function testDecodingReadHandleTryReadOnEof(): void
    {
        $inner = new IO\MemoryHandle('');
        $handle = new Hex\DecodingReadHandle($inner);

        static::assertSame('', $handle->tryRead());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testEncodingReadHandleReadAfterEof(): void
    {
        $inner = new IO\MemoryHandle('A');
        $handle = new Hex\EncodingReadHandle($inner);

        $handle->readAll();
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testEncodingReadHandleReadByteOnEmpty(): void
    {
        $inner = new IO\MemoryHandle('');
        $handle = new Hex\EncodingReadHandle($inner);

        $this->expectException(IO\Exception\RuntimeException::class);
        $handle->readByte();
    }

    public function testDecodingWriteHandleOddFlushThrows(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Hex\DecodingWriteHandle($inner);

        $handle->write('a');

        $this->expectException(Exception\RangeException::class);
        $handle->flush();
    }

    public function testEncodingWriteHandleRoundTrip(): void
    {
        $data = "\x00\x01\xff";
        $encoded = new IO\MemoryHandle();
        $encoder = new Hex\EncodingWriteHandle($encoded);
        $encoder->writeAll($data);

        $encoded->seek(0);
        $decoder = new Hex\DecodingReadHandle($encoded);
        static::assertSame($data, $decoder->readAll());
    }
}
