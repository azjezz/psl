<?php

declare(strict_types=1);

namespace Psl\Encoding\Tests\Unit\Base64;

use PHPUnit\Framework\TestCase;
use Psl\Encoding\Base64;
use Psl\Encoding\Exception;
use Psl\IO;

use function str_repeat;
use function strlen;

final class WriteHandleTest extends TestCase
{
    public function testDecodingWriteChunkedInputWithRemainder(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Base64\DecodingWriteHandle($inner);
        $handle->write('SG');
        $handle->write('Vs');
        $handle->write('bG8=');
        $handle->flush();
        $inner->seek(0);
        static::assertSame('Hello', $inner->readAll());
    }

    public function testDecodingWriteFlushEmptyNoOutput(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Base64\DecodingWriteHandle($inner);
        $handle->flush();
        $inner->seek(0);
        static::assertSame('', $inner->readAll());
    }

    public function testDecodingWriteFlushWithData(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Base64\DecodingWriteHandle($inner);
        $handle->write('SGVsbG8=');
        $handle->flush();
        $inner->seek(0);
        static::assertSame('Hello', $inner->readAll());
    }

    public function testDecodingWritePaddingTrue(): void
    {
        $data = 'Hi';
        $encoded = Base64\encode($data, padding: true);
        $inner = new IO\MemoryHandle();
        $handle = new Base64\DecodingWriteHandle($inner, padding: true);
        $handle->writeAll($encoded);
        $handle->flush();
        $inner->seek(0);
        static::assertSame($data, $inner->readAll());
    }

    public function testDecodingWritePaddingFalse(): void
    {
        $data = 'Hi';
        $encoded = Base64\encode($data, padding: false);
        $inner = new IO\MemoryHandle();
        $handle = new Base64\DecodingWriteHandle($inner, padding: false);
        $handle->writeAll($encoded);
        $handle->flush();
        $inner->seek(0);
        static::assertSame($data, $inner->readAll());
    }

    public function testDecodingWriteRemainderConcatOrder(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Base64\DecodingWriteHandle($inner);
        $handle->write('SG');
        $handle->write('Vs');
        $inner->seek(0);
        static::assertSame('Hel', $inner->readAll());
        $handle->flush();
    }

    public function testDecodingWriteSingleCharNoOutput(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Base64\DecodingWriteHandle($inner);
        $handle->write('S');
        $inner->seek(0);
        static::assertSame('', $inner->readAll());
    }

    public function testDecodingWriteInvalidBase64Throws(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Base64\DecodingWriteHandle($inner);
        $handle->writeAll('!!!');
        $this->expectException(Exception\RangeException::class);
        $handle->flush();
    }

    public function testDecodingWriteWithVariant(): void
    {
        $data = 'Hello+World/Test';
        $encoded = Base64\encode($data, Base64\Variant::UrlSafe);
        $inner = new IO\MemoryHandle();
        $handle = new Base64\DecodingWriteHandle($inner, Base64\Variant::UrlSafe);
        $handle->writeAll($encoded);
        $handle->flush();
        $inner->seek(0);
        static::assertSame($data, $inner->readAll());
    }

    public function testEncodingWriteChunkedInput(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Base64\EncodingWriteHandle($inner);
        $handle->write('Hello');
        $handle->write(', World!');
        $handle->flush();
        $inner->seek(0);
        $decoder = new Base64\DecodingReadHandle(new IO\MemoryHandle($inner->readAll()));
        static::assertSame('Hello, World!', $decoder->readAll());
    }

    public function testEncodingWriteFlushEmptyNoOutput(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Base64\EncodingWriteHandle($inner);
        $handle->flush();
        $inner->seek(0);
        static::assertSame('', $inner->readAll());
    }

    public function testEncodingWriteFlushWithData(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Base64\EncodingWriteHandle($inner);
        $handle->write('Hello');
        $handle->flush();
        $inner->seek(0);
        $decoder = new Base64\DecodingReadHandle(new IO\MemoryHandle($inner->readAll()));
        static::assertSame('Hello', $decoder->readAll());
    }

    public function testEncodingWriteExactChunkSize(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Base64\EncodingWriteHandle($inner);
        $handle->write(str_repeat('A', 57));
        $inner->seek(0);
        $encoded = $inner->readAll();
        static::assertGreaterThan(0, strlen($encoded));
    }

    public function testEncodingWriteWithVariant(): void
    {
        $data = 'Hello';
        $inner = new IO\MemoryHandle();
        $handle = new Base64\EncodingWriteHandle($inner, Base64\Variant::UrlSafe);
        $handle->writeAll($data);
        $handle->flush();
        $inner->seek(0);
        $decoder = new Base64\DecodingReadHandle(new IO\MemoryHandle($inner->readAll()), Base64\Variant::UrlSafe);
        static::assertSame($data, $decoder->readAll());
    }

    public function testEncodingWriteNoPadding(): void
    {
        $data = 'Hi';
        $inner = new IO\MemoryHandle();
        $handle = new Base64\EncodingWriteHandle($inner, padding: false);
        $handle->writeAll($data);
        $handle->flush();
        $inner->seek(0);
        $decoder = new Base64\DecodingReadHandle(new IO\MemoryHandle($inner->readAll()), padding: false);
        static::assertSame($data, $decoder->readAll());
    }
}
