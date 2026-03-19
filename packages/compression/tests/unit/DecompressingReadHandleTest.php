<?php

declare(strict_types=1);

namespace Psl\Compression\Tests\Unit;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Psl\Compression\CompressingReadHandle;
use Psl\Compression\DecompressingReadHandle;
use Psl\Compression\Tests\Fixture\BrotliCompressor;
use Psl\Compression\Tests\Fixture\BrotliDecompressor;
use Psl\IO;

use function str_repeat;
use function strlen;

#[RequiresPhpExtension('brotli')]
final class DecompressingReadHandleTest extends TestCase
{
    public function testReadAll(): void
    {
        $original = 'hello world';
        $compressed = $this->compress($original);

        $reader = new DecompressingReadHandle(new IO\MemoryHandle($compressed), new BrotliDecompressor());

        static::assertSame($original, $reader->readAll());
    }

    public function testReadWithMaxBytes(): void
    {
        $original = 'hello world';
        $compressed = $this->compress($original);

        $reader = new DecompressingReadHandle(new IO\MemoryHandle($compressed), new BrotliDecompressor());

        $first = $reader->read(5);

        static::assertSame('hello', $first);
        static::assertFalse($reader->reachedEndOfDataSource());

        $rest = $reader->readAll();

        static::assertSame(' world', $rest);
        static::assertTrue($reader->reachedEndOfDataSource());
    }

    public function testTryRead(): void
    {
        $compressed = $this->compress('hello world');

        $reader = new DecompressingReadHandle(new IO\MemoryHandle($compressed), new BrotliDecompressor());

        $result = $reader->tryRead();

        static::assertNotSame('', $result);
    }

    public function testTryReadWithMaxBytes(): void
    {
        $compressed = $this->compress('hello world');

        $reader = new DecompressingReadHandle(new IO\MemoryHandle($compressed), new BrotliDecompressor());

        $chunk = $reader->tryRead(3);

        static::assertSame(3, strlen($chunk));
        static::assertSame('hel', $chunk);
    }

    public function testReachedEndOfDataSource(): void
    {
        $compressed = $this->compress('data');

        $reader = new DecompressingReadHandle(new IO\MemoryHandle($compressed), new BrotliDecompressor());

        static::assertFalse($reader->reachedEndOfDataSource());

        $reader->readAll();

        static::assertTrue($reader->reachedEndOfDataSource());
    }

    public function testEmptyInput(): void
    {
        $compressed = $this->compress('');

        $reader = new DecompressingReadHandle(new IO\MemoryHandle($compressed), new BrotliDecompressor());

        static::assertSame('', $reader->readAll());
        static::assertTrue($reader->reachedEndOfDataSource());
    }

    public function testReadFixedSize(): void
    {
        $compressed = $this->compress('hello world');

        $reader = new DecompressingReadHandle(new IO\MemoryHandle($compressed), new BrotliDecompressor());

        static::assertSame('hello', $reader->readFixedSize(5));
        static::assertSame(' worl', $reader->readFixedSize(5));
    }

    public function testLargeDataRoundtrip(): void
    {
        $original = str_repeat('The quick brown fox jumps over the lazy dog. ', 1000);
        $compressed = $this->compress($original);

        $reader = new DecompressingReadHandle(new IO\MemoryHandle($compressed), new BrotliDecompressor());

        static::assertSame($original, $reader->readAll());
    }

    public function testReadAllFromPipe(): void
    {
        $original = 'hello from a pipe';
        $compressed = $this->compress($original);

        [$read, $write] = IO\pipe();
        $write->writeAll($compressed);
        $write->close();

        $reader = new DecompressingReadHandle($read, new BrotliDecompressor());

        static::assertSame($original, $reader->readAll());
        static::assertTrue($reader->reachedEndOfDataSource());
    }

    public function testReadWithMaxBytesFromPipe(): void
    {
        $original = 'pipe data here';
        $compressed = $this->compress($original);

        [$read, $write] = IO\pipe();
        $write->writeAll($compressed);
        $write->close();

        $reader = new DecompressingReadHandle($read, new BrotliDecompressor());

        static::assertSame('pipe', $reader->read(4));
        static::assertSame(' data here', $reader->readAll());
    }

    public function testWrappedWithReaderForBufferedMethods(): void
    {
        $original = "first\nsecond\nthird\n";
        $compressed = $this->compress($original);

        $handle = new DecompressingReadHandle(new IO\MemoryHandle($compressed), new BrotliDecompressor());
        $reader = new IO\Reader($handle);

        static::assertSame('first', $reader->readLine());
        static::assertSame('second', $reader->readLine());
        static::assertSame('third', $reader->readLine());
        static::assertNull($reader->readLine());
    }

    public function testWrappedWithReaderReadUntil(): void
    {
        $original = 'key=value;other=data;end';
        $compressed = $this->compress($original);

        $handle = new DecompressingReadHandle(new IO\MemoryHandle($compressed), new BrotliDecompressor());
        $reader = new IO\Reader($handle);

        static::assertSame('key=value', $reader->readUntil(';'));
        static::assertSame('other=data', $reader->readUntil(';'));
        static::assertNull($reader->readUntil(';'));
    }

    public function testWrappedWithReaderReadByte(): void
    {
        $compressed = $this->compress('ABC');

        $handle = new DecompressingReadHandle(new IO\MemoryHandle($compressed), new BrotliDecompressor());
        $reader = new IO\Reader($handle);

        static::assertSame('A', $reader->readByte());
        static::assertSame('B', $reader->readByte());
        static::assertSame('C', $reader->readByte());
    }

    public function testSmallChunkSize(): void
    {
        $original = str_repeat('chunk test data ', 100);
        $compressed = $this->compress($original);

        $reader = new DecompressingReadHandle(
            new IO\MemoryHandle($compressed),
            new BrotliDecompressor(),
            chunkSize: 16,
        );

        static::assertSame($original, $reader->readAll());
    }

    public function testLargeChunkSize(): void
    {
        $original = str_repeat('chunk test data ', 100);
        $compressed = $this->compress($original);

        $reader = new DecompressingReadHandle(
            new IO\MemoryHandle($compressed),
            new BrotliDecompressor(),
            chunkSize: 65_536,
        );

        static::assertSame($original, $reader->readAll());
    }

    public function testChunkSizeOneByteAtATime(): void
    {
        $original = 'hello world';
        $compressed = $this->compress($original);

        $reader = new DecompressingReadHandle(new IO\MemoryHandle($compressed), new BrotliDecompressor(), chunkSize: 1);

        static::assertSame($original, $reader->readAll());
    }

    public function testChunkSizeIsPassedToInnerRead(): void
    {
        $compressed = $this->compress('hello world');
        $called = false;

        $inner = $this->createStub(IO\ReadHandleInterface::class);
        $inner
            ->method('read')
            ->willReturnCallback(static function (null|int $maxBytes) use (&$called, $compressed): string {
                static::assertSame(256, $maxBytes);
                if (!$called) {
                    $called = true;
                    return $compressed;
                }

                return '';
            });
        $inner->method('reachedEndOfDataSource')->willReturnCallback(static fn(): bool => $called);

        $reader = new DecompressingReadHandle($inner, new BrotliDecompressor(), chunkSize: 256);
        $reader->read();

        static::assertTrue($called);
    }

    public function testChunkSizeIsPassedToInnerTryRead(): void
    {
        $compressed = $this->compress('hello world');
        $called = false;

        $inner = $this->createStub(IO\ReadHandleInterface::class);
        $inner
            ->method('tryRead')
            ->willReturnCallback(static function (null|int $maxBytes) use (&$called, $compressed): string {
                static::assertSame(512, $maxBytes);
                if (!$called) {
                    $called = true;
                    return $compressed;
                }

                return '';
            });
        $inner->method('reachedEndOfDataSource')->willReturnCallback(static fn(): bool => $called);

        $reader = new DecompressingReadHandle($inner, new BrotliDecompressor(), chunkSize: 512);
        $reader->tryRead();

        static::assertTrue($called);
    }

    public function testDefaultChunkSizeIs8192(): void
    {
        $compressed = $this->compress('hello world');
        $called = false;

        $inner = $this->createStub(IO\ReadHandleInterface::class);
        $inner
            ->method('read')
            ->willReturnCallback(static function (null|int $maxBytes) use (&$called, $compressed): string {
                static::assertSame(8192, $maxBytes);
                if (!$called) {
                    $called = true;
                    return $compressed;
                }

                return '';
            });
        $inner->method('reachedEndOfDataSource')->willReturnCallback(static fn(): bool => $called);

        $reader = new DecompressingReadHandle($inner, new BrotliDecompressor());
        $reader->read();

        static::assertTrue($called);
    }

    public function testDifferentChunkSizesProduceSameResult(): void
    {
        $original = str_repeat('The quick brown fox. ', 200);
        $compressed = $this->compress($original);

        $result16 = new DecompressingReadHandle(
            new IO\MemoryHandle($compressed),
            new BrotliDecompressor(),
            chunkSize: 16,
        )->readAll();
        $result512 = new DecompressingReadHandle(
            new IO\MemoryHandle($compressed),
            new BrotliDecompressor(),
            chunkSize: 512,
        )->readAll();
        $result8192 = new DecompressingReadHandle(
            new IO\MemoryHandle($compressed),
            new BrotliDecompressor(),
            chunkSize: 8192,
        )->readAll();
        $result65536 = new DecompressingReadHandle(
            new IO\MemoryHandle($compressed),
            new BrotliDecompressor(),
            chunkSize: 65_536,
        )->readAll();

        static::assertSame($original, $result16);
        static::assertSame($original, $result512);
        static::assertSame($original, $result8192);
        static::assertSame($original, $result65536);
    }

    private function compress(string $data): string
    {
        $reader = new CompressingReadHandle(new IO\MemoryHandle($data), new BrotliCompressor());

        return $reader->readAll();
    }
}
