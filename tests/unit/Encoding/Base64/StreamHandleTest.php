<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Encoding\Base64;

use PHPUnit\Framework\TestCase;
use Psl\Encoding\Base64;
use Psl\Encoding\Exception;
use Psl\IO;

final class StreamHandleTest extends TestCase
{
    public function testEncodingReadHandle(): void
    {
        $inner = new IO\MemoryHandle('Hello, World!');
        $handle = new Base64\EncodingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("SGVsbG8sIFdvcmxkIQ==\r\n", $result);
    }

    public function testDecodingReadHandle(): void
    {
        $inner = new IO\MemoryHandle('SGVsbG8sIFdvcmxkIQ==');
        $handle = new Base64\DecodingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame('Hello, World!', $result);
    }

    public function testEncodingWriteHandle(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Base64\EncodingWriteHandle($inner);

        $handle->writeAll('Hello, World!');
        $handle->flush();

        $inner->seek(0);
        static::assertSame("SGVsbG8sIFdvcmxkIQ==\r\n", $inner->readAll());
    }

    public function testDecodingWriteHandle(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Base64\DecodingWriteHandle($inner);

        $handle->writeAll('SGVsbG8sIFdvcmxkIQ==');
        $handle->flush();

        $inner->seek(0);
        static::assertSame('Hello, World!', $inner->readAll());
    }

    public function testRoundTripReadHandles(): void
    {
        $original = 'The quick brown fox jumps over the lazy dog.';

        $raw = new IO\MemoryHandle($original);
        $encoding = new Base64\EncodingReadHandle($raw);
        $encoded = $encoding->readAll();

        $encodedHandle = new IO\MemoryHandle($encoded);
        $decoding = new Base64\DecodingReadHandle($encodedHandle);
        $decoded = $decoding->readAll();

        static::assertSame($original, $decoded);
    }

    public function testRoundTripWriteHandles(): void
    {
        $original = 'The quick brown fox jumps over the lazy dog.';

        $encodedBuffer = new IO\MemoryHandle();
        $encoding = new Base64\EncodingWriteHandle($encodedBuffer);
        $encoding->writeAll($original);
        $encoding->flush();

        $decodedBuffer = new IO\MemoryHandle();
        $decoding = new Base64\DecodingWriteHandle($decodedBuffer);
        $encodedBuffer->seek(0);
        $decoding->writeAll($encodedBuffer->readAll());
        $decoding->flush();

        $decodedBuffer->seek(0);
        static::assertSame($original, $decodedBuffer->readAll());
    }

    public function testLargeDataChunking(): void
    {
        $original = str_repeat('ABCDEFGHIJ', 100);

        $raw = new IO\MemoryHandle($original);
        $encoding = new Base64\EncodingReadHandle($raw);
        $encoded = $encoding->readAll();

        $encodedHandle = new IO\MemoryHandle($encoded);
        $decoding = new Base64\DecodingReadHandle($encodedHandle);
        $decoded = $decoding->readAll();

        static::assertSame($original, $decoded);
    }

    public function testDecodingReadHandleInvalidBase64(): void
    {
        $inner = new IO\MemoryHandle('!!!invalid!!!');
        $handle = new Base64\DecodingReadHandle($inner);

        $this->expectException(Exception\RangeException::class);

        $handle->readAll();
    }

    public function testDecodingWriteHandleInvalidBase64(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Base64\DecodingWriteHandle($inner);

        $handle->writeAll('!!!');

        $this->expectException(Exception\RangeException::class);

        $handle->flush();
    }

    public function testEncodingReadHandleEmpty(): void
    {
        $inner = new IO\MemoryHandle('');
        $handle = new Base64\EncodingReadHandle($inner);

        static::assertSame('', $handle->readAll());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testDecodingReadHandleEmpty(): void
    {
        $inner = new IO\MemoryHandle('');
        $handle = new Base64\DecodingReadHandle($inner);

        static::assertSame('', $handle->readAll());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testEncodingReadHandleBinaryData(): void
    {
        $binary = "\x00\x01\x02\xff\xfe\xfd";
        $inner = new IO\MemoryHandle($binary);
        $handle = new Base64\EncodingReadHandle($inner);

        $encoded = $handle->readAll();

        $decodingHandle = new IO\MemoryHandle($encoded);
        $decoding = new Base64\DecodingReadHandle($decodingHandle);

        static::assertSame($binary, $decoding->readAll());
    }

    public function testDecodingReadHandleWithWhitespace(): void
    {
        $inner = new IO\MemoryHandle("SGVs\r\nbG8s\r\nIFdv\r\ncmxk\r\nIQ==");
        $handle = new Base64\DecodingReadHandle($inner);

        static::assertSame('Hello, World!', $handle->readAll());
    }

    public function testPartialReads(): void
    {
        $inner = new IO\MemoryHandle('SGVsbG8sIFdvcmxkIQ==');
        $handle = new Base64\DecodingReadHandle($inner);

        $result = '';
        while (!$handle->reachedEndOfDataSource()) {
            $chunk = $handle->read(3);
            $result .= $chunk;
        }

        static::assertSame('Hello, World!', $result);
    }

    public function testDecodingReadHandleReadByte(): void
    {
        $inner = new IO\MemoryHandle(base64_encode('ABC'));
        $handle = new Base64\DecodingReadHandle($inner);

        static::assertSame('A', $handle->readByte());
        static::assertSame('B', $handle->readByte());
        static::assertSame('C', $handle->readByte());
    }

    public function testDecodingReadHandleReadByteOnEmpty(): void
    {
        $inner = new IO\MemoryHandle('');
        $handle = new Base64\DecodingReadHandle($inner);

        $this->expectException(IO\Exception\RuntimeException::class);

        $handle->readByte();
    }

    public function testDecodingReadHandleReadLine(): void
    {
        $inner = new IO\MemoryHandle(base64_encode('line1' . PHP_EOL . 'line2' . PHP_EOL . 'line3'));
        $handle = new Base64\DecodingReadHandle($inner);

        static::assertSame('line1', $handle->readLine());
        static::assertSame('line2', $handle->readLine());
        static::assertSame('line3', $handle->readLine());
        static::assertNull($handle->readLine());
    }

    public function testDecodingReadHandleReadLineNoNewline(): void
    {
        $inner = new IO\MemoryHandle(base64_encode('no newline'));
        $handle = new Base64\DecodingReadHandle($inner);

        static::assertSame('no newline', $handle->readLine());
        static::assertNull($handle->readLine());
    }

    public function testDecodingReadHandleReadUntil(): void
    {
        $inner = new IO\MemoryHandle(base64_encode('key=value&other=data'));
        $handle = new Base64\DecodingReadHandle($inner);

        static::assertSame('key=value', $handle->readUntil('&'));
        static::assertNull($handle->readUntil('&'));
    }

    public function testDecodingReadHandleReadUntilNotFound(): void
    {
        $inner = new IO\MemoryHandle(base64_encode('no delimiter here'));
        $handle = new Base64\DecodingReadHandle($inner);

        static::assertNull($handle->readUntil('@'));
    }

    public function testDecodingReadHandleReadUntilBounded(): void
    {
        $inner = new IO\MemoryHandle(base64_encode('short:end'));
        $handle = new Base64\DecodingReadHandle($inner);

        static::assertSame('short', $handle->readUntilBounded(':end', 10));
    }

    public function testDecodingReadHandleReadUntilBoundedOverflow(): void
    {
        $inner = new IO\MemoryHandle(base64_encode('toolongcontent:end'));
        $handle = new Base64\DecodingReadHandle($inner);

        $this->expectException(IO\Exception\OverflowException::class);

        $handle->readUntilBounded(':end', 3);
    }

    public function testDecodingReadHandleReadUntilBoundedNotFound(): void
    {
        $inner = new IO\MemoryHandle(base64_encode('no suffix'));
        $handle = new Base64\DecodingReadHandle($inner);

        static::assertNull($handle->readUntilBounded('@', 100));
    }

    public function testEncodingReadHandleReadByte(): void
    {
        $inner = new IO\MemoryHandle('A');
        $handle = new Base64\EncodingReadHandle($inner);

        static::assertSame('Q', $handle->readByte());
        static::assertSame('Q', $handle->readByte());
    }

    public function testEncodingReadHandleReadLine(): void
    {
        $inner = new IO\MemoryHandle('Hello, World!');
        $handle = new Base64\EncodingReadHandle($inner);

        $line = $handle->readLine();

        static::assertNotNull($line);
        static::assertStringContainsString('SGVsbG8sIFdvcmxkIQ==', $line);
    }

    public function testEncodingReadHandleReadUntil(): void
    {
        $inner = new IO\MemoryHandle('Hello');
        $handle = new Base64\EncodingReadHandle($inner);

        $result = $handle->readUntil('=');

        static::assertNotNull($result);
        static::assertSame('SGVsbG8', $result);
    }
}
