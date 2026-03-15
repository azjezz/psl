<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Encoding\QuotedPrintable;

use PHPUnit\Framework\TestCase;
use Psl\Encoding\QuotedPrintable;
use Psl\IO;

final class StreamHandleTest extends TestCase
{
    public function testEncodingReadHandlePlainAscii(): void
    {
        $inner = new IO\MemoryHandle('Hello, World!');
        $handle = new QuotedPrintable\EncodingReadHandle($inner);

        static::assertSame('Hello, World!', $handle->readAll());
    }

    public function testEncodingReadHandleMultiLine(): void
    {
        $inner = new IO\MemoryHandle("line1\r\nline2");
        $handle = new QuotedPrintable\EncodingReadHandle($inner);

        static::assertSame("line1\r\nline2", $handle->readAll());
    }

    public function testEncodingReadHandleSpecialChars(): void
    {
        $inner = new IO\MemoryHandle('3+3=6');
        $handle = new QuotedPrintable\EncodingReadHandle($inner);

        static::assertSame('3+3=3D6', $handle->readAll());
    }

    public function testEncodingReadHandleTrailingWhitespace(): void
    {
        $inner = new IO\MemoryHandle('trailing space ');
        $handle = new QuotedPrintable\EncodingReadHandle($inner);

        static::assertSame('trailing space=20', $handle->readAll());
    }

    public function testEncodingReadHandleLongLine(): void
    {
        $inner = new IO\MemoryHandle(str_repeat('A', 100));
        $handle = new QuotedPrintable\EncodingReadHandle($inner);

        $result = $handle->readAll();

        $lines = explode("\r\n", $result);
        foreach ($lines as $line) {
            static::assertLessThanOrEqual(76, strlen($line));
        }
    }

    public function testEncodingReadHandleEmpty(): void
    {
        $inner = new IO\MemoryHandle('');
        $handle = new QuotedPrintable\EncodingReadHandle($inner);

        static::assertSame('', $handle->readAll());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testDecodingReadHandlePlain(): void
    {
        $inner = new IO\MemoryHandle('Hello, World!');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('Hello, World!', $handle->readAll());
    }

    public function testDecodingReadHandleEncoded(): void
    {
        $inner = new IO\MemoryHandle("Hello=20World\r\nLine=20two");
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame("Hello World\r\nLine two", $handle->readAll());
    }

    public function testDecodingReadHandleSoftBreak(): void
    {
        $inner = new IO\MemoryHandle("Hello=\r\n World");
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('Hello World', $handle->readAll());
    }

    public function testDecodingReadHandleEmpty(): void
    {
        $inner = new IO\MemoryHandle('');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('', $handle->readAll());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testEncodingWriteHandlePlain(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new QuotedPrintable\EncodingWriteHandle($inner);

        $handle->writeAll('Hello');
        $handle->flush();

        $inner->seek(0);
        static::assertSame('Hello', $inner->readAll());
    }

    public function testEncodingWriteHandleMultiLine(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new QuotedPrintable\EncodingWriteHandle($inner);

        $handle->writeAll("line1\r\nline2");
        $handle->flush();

        $inner->seek(0);
        static::assertSame("line1\r\nline2", $inner->readAll());
    }

    public function testEncodingWriteHandleSpecialChars(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new QuotedPrintable\EncodingWriteHandle($inner);

        $handle->writeAll("3+3=6\r\nnext");
        $handle->flush();

        $inner->seek(0);
        static::assertSame("3+3=3D6\r\nnext", $inner->readAll());
    }

    public function testDecodingWriteHandlePlain(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new QuotedPrintable\DecodingWriteHandle($inner);

        $handle->writeAll("Hello=20World\r\n");
        $handle->flush();

        $inner->seek(0);
        static::assertSame('Hello World', $inner->readAll());
    }

    public function testDecodingWriteHandleSoftBreak(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new QuotedPrintable\DecodingWriteHandle($inner);

        $handle->writeAll("Hello=\r\nWorld\r\n");
        $handle->flush();

        $inner->seek(0);
        static::assertSame('HelloWorld', $inner->readAll());
    }

    public function testRoundTripReadHandles(): void
    {
        $original = "Subject: Hello =World!\r\nLine 2 with trailing space ";

        $raw = new IO\MemoryHandle($original);
        $encoding = new QuotedPrintable\EncodingReadHandle($raw);
        $encoded = $encoding->readAll();

        $encodedHandle = new IO\MemoryHandle($encoded);
        $decoding = new QuotedPrintable\DecodingReadHandle($encodedHandle);

        static::assertSame($original, $decoding->readAll());
    }

    public function testRoundTripWriteHandles(): void
    {
        $original = "Hello =World!\r\nLine 2";

        $encodedBuffer = new IO\MemoryHandle();
        $encoding = new QuotedPrintable\EncodingWriteHandle($encodedBuffer);
        $encoding->writeAll($original);
        $encoding->flush();

        $decodedBuffer = new IO\MemoryHandle();
        $decoding = new QuotedPrintable\DecodingWriteHandle($decodedBuffer);
        $encodedBuffer->seek(0);
        $decoding->writeAll($encodedBuffer->readAll());
        $decoding->flush();

        $decodedBuffer->seek(0);
        static::assertSame($original, $decodedBuffer->readAll());
    }

    public function testDecodingReadHandleReadByte(): void
    {
        $inner = new IO\MemoryHandle('ABC');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('A', $handle->readByte());
        static::assertSame('B', $handle->readByte());
        static::assertSame('C', $handle->readByte());
    }

    public function testDecodingReadHandleReadByteOnEmpty(): void
    {
        $inner = new IO\MemoryHandle('');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        $this->expectException(IO\Exception\RuntimeException::class);

        $handle->readByte();
    }

    public function testDecodingReadHandleReadByteEncoded(): void
    {
        $inner = new IO\MemoryHandle('=41=42');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('A', $handle->readByte());
        static::assertSame('B', $handle->readByte());
    }

    public function testDecodingReadHandleReadLine(): void
    {
        $inner = new IO\MemoryHandle("line=20one\r\nline=20two\r\nline=20three");
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame("line one\r\nline two", $handle->readUntil("\r\nline three"));
    }

    public function testDecodingReadHandleReadUntil(): void
    {
        $inner = new IO\MemoryHandle('key=3Dvalue&other=3Ddata');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('key=value', $handle->readUntil('&'));
        static::assertNull($handle->readUntil('&'));
    }

    public function testDecodingReadHandleReadUntilNotFound(): void
    {
        $inner = new IO\MemoryHandle('hello=20world');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertNull($handle->readUntil('@'));
    }

    public function testDecodingReadHandleReadUntilBounded(): void
    {
        $inner = new IO\MemoryHandle('short:end');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('short', $handle->readUntilBounded(':end', 10));
    }

    public function testDecodingReadHandleReadUntilBoundedOverflow(): void
    {
        $inner = new IO\MemoryHandle('toolongcontent:end');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        $this->expectException(IO\Exception\OverflowException::class);

        $handle->readUntilBounded(':end', 3);
    }

    public function testDecodingReadHandleReadUntilBoundedNotFound(): void
    {
        $inner = new IO\MemoryHandle('nothing');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertNull($handle->readUntilBounded('@', 100));
    }

    public function testEncodingReadHandleReadByte(): void
    {
        $inner = new IO\MemoryHandle('=');
        $handle = new QuotedPrintable\EncodingReadHandle($inner);

        static::assertSame('=', $handle->readByte());
        static::assertSame('3', $handle->readByte());
        static::assertSame('D', $handle->readByte());
    }

    public function testEncodingReadHandleReadUntil(): void
    {
        $inner = new IO\MemoryHandle('Hello');
        $handle = new QuotedPrintable\EncodingReadHandle($inner);

        static::assertSame('He', $handle->readUntil('ll'));
        static::assertSame('o', $handle->readAll());
    }

    public function testDecodingReadHandleInterleavedMethods(): void
    {
        $inner = new IO\MemoryHandle("abc|def\r\nghi");
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('abc', $handle->readUntil('|'));
        static::assertSame('d', $handle->readByte());
        static::assertSame('ef', $handle->readUntil("\r\n"));
        static::assertSame('ghi', $handle->readAll());
    }

    public function testDecodingReadHandleSoftBreakThenReadByte(): void
    {
        $inner = new IO\MemoryHandle("AB=\r\nCD");
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('A', $handle->readByte());
        static::assertSame('B', $handle->readByte());
        static::assertSame('C', $handle->readByte());
        static::assertSame('D', $handle->readByte());
    }

    public function testEncodingReadHandleReadByteOnEmpty(): void
    {
        $inner = new IO\MemoryHandle('');
        $handle = new QuotedPrintable\EncodingReadHandle($inner);

        $this->expectException(IO\Exception\RuntimeException::class);

        $handle->readByte();
    }

    public function testDecodingReadHandleTryReadExactBuffer(): void
    {
        $inner = new IO\MemoryHandle('ABCD');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('ABCD', $handle->tryRead(4));
        static::assertSame('', $handle->tryRead());
    }

    public function testDecodingReadHandleReadAfterEof(): void
    {
        $inner = new IO\MemoryHandle('X');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('X', $handle->read());
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testDecodingReadHandleReadByteSingle(): void
    {
        $inner = new IO\MemoryHandle('X');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('X', $handle->readByte());
    }

    public function testDecodingReadHandleReadLineMultiple(): void
    {
        $inner = new IO\MemoryHandle("abc\r\ndef");
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        $first = $handle->readLine();
        static::assertNotNull($first);
        static::assertStringContainsString('abc', $first);
    }

    public function testDecodingReadHandleReadUntilMultiple(): void
    {
        $inner = new IO\MemoryHandle('a|b|c');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('a', $handle->readUntil('|'));
        static::assertSame('b', $handle->readUntil('|'));
        static::assertNull($handle->readUntil('|'));
    }

    public function testDecodingReadHandleReadUntilBoundedExact(): void
    {
        $inner = new IO\MemoryHandle('abcde:end');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('abcde', $handle->readUntilBounded(':end', 5));
    }

    public function testDecodingReadHandleTryReadOnEof(): void
    {
        $inner = new IO\MemoryHandle('');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('', $handle->tryRead());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testEncodingReadHandleReadAfterEof(): void
    {
        $inner = new IO\MemoryHandle('A');
        $handle = new QuotedPrintable\EncodingReadHandle($inner);

        $handle->readAll();
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testEncodingReadHandleTryReadExact(): void
    {
        $inner = new IO\MemoryHandle('AB');
        $handle = new QuotedPrintable\EncodingReadHandle($inner);

        static::assertSame('A', $handle->tryRead(1));
        static::assertSame('B', $handle->tryRead());
    }

    public function testDecodingWriteHandleFlushEmpty(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new QuotedPrintable\DecodingWriteHandle($inner);

        $handle->flush();

        $inner->seek(0);
        static::assertSame('', $inner->readAll());
    }

    public function testEncodingWriteHandleFlushEmpty(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new QuotedPrintable\EncodingWriteHandle($inner);

        $handle->flush();

        $inner->seek(0);
        static::assertSame('', $inner->readAll());
    }

    public function testDecodingReadHandleReadLineThenNull(): void
    {
        $inner = new IO\MemoryHandle('no newline');
        $handle = new QuotedPrintable\DecodingReadHandle($inner);

        static::assertSame('no newline', $handle->readLine());
        static::assertNull($handle->readLine());
    }

    public function testEncodingReadHandleReadLineThenNull(): void
    {
        $inner = new IO\MemoryHandle('no newline');
        $handle = new QuotedPrintable\EncodingReadHandle($inner);

        static::assertSame('no newline', $handle->readLine());
        static::assertNull($handle->readLine());
    }
}
