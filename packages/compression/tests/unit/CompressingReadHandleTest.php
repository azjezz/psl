<?php

declare(strict_types=1);

namespace Psl\Compression\Tests\Unit;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Psl\Compression\CompressingReadHandle;
use Psl\Compression\Tests\Fixture\BrotliCompressor;
use Psl\IO;

use function brotli_uncompress;
use function str_repeat;
use function strlen;

#[RequiresPhpExtension('brotli')]
final class CompressingReadHandleTest extends TestCase
{
    public function testReadAll(): void
    {
        $original = 'hello world';
        $reader = new CompressingReadHandle(new IO\MemoryHandle($original), new BrotliCompressor());

        $compressed = $reader->readAll();

        static::assertSame($original, brotli_uncompress($compressed));
    }

    public function testReadWithMaxBytes(): void
    {
        $original = 'hello world';
        $reader = new CompressingReadHandle(new IO\MemoryHandle($original), new BrotliCompressor());

        $first = $reader->read(5);

        static::assertSame(5, strlen($first));
        static::assertFalse($reader->reachedEndOfDataSource());

        $rest = $reader->readAll();

        static::assertSame($original, brotli_uncompress($first . $rest));
        static::assertTrue($reader->reachedEndOfDataSource());
    }

    public function testTryRead(): void
    {
        $original = 'hello world';
        $reader = new CompressingReadHandle(new IO\MemoryHandle($original), new BrotliCompressor());

        $result = $reader->tryRead();

        static::assertNotSame('', $result);
    }

    public function testTryReadWithMaxBytes(): void
    {
        $original = 'hello world';
        $reader = new CompressingReadHandle(new IO\MemoryHandle($original), new BrotliCompressor());

        $chunk = $reader->tryRead(3);

        static::assertSame(3, strlen($chunk));
    }

    public function testReachedEndOfDataSource(): void
    {
        $reader = new CompressingReadHandle(new IO\MemoryHandle('data'), new BrotliCompressor());

        static::assertFalse($reader->reachedEndOfDataSource());

        $reader->readAll();

        static::assertTrue($reader->reachedEndOfDataSource());
    }

    public function testReachedEndOfDataSourceOnEmptyInput(): void
    {
        $reader = new CompressingReadHandle(new IO\MemoryHandle(''), new BrotliCompressor());

        static::assertFalse($reader->reachedEndOfDataSource());

        $reader->readAll();

        static::assertTrue($reader->reachedEndOfDataSource());
    }

    public function testEmptyInput(): void
    {
        $reader = new CompressingReadHandle(new IO\MemoryHandle(''), new BrotliCompressor());

        $compressed = $reader->readAll();

        // Even empty input produces compressed output (brotli header/footer)
        static::assertSame('', brotli_uncompress($compressed));
    }

    public function testLargeDataRoundtrip(): void
    {
        $original = str_repeat('The quick brown fox jumps over the lazy dog. ', 1000);

        $reader = new CompressingReadHandle(new IO\MemoryHandle($original), new BrotliCompressor());
        $compressed = $reader->readAll();

        static::assertLessThan(strlen($original), strlen($compressed));
        static::assertSame($original, brotli_uncompress($compressed));
    }

    public function testReadFixedSize(): void
    {
        $reader = new CompressingReadHandle(new IO\MemoryHandle('hello world'), new BrotliCompressor());

        $chunk = $reader->readFixedSize(4);

        static::assertSame(4, strlen($chunk));
    }

    public function testReadAllFromPipe(): void
    {
        $original = 'hello from a pipe';
        [$read, $write] = IO\pipe();
        $write->writeAll($original);
        $write->close();

        $reader = new CompressingReadHandle($read, new BrotliCompressor());
        $compressed = $reader->readAll();

        static::assertSame($original, brotli_uncompress($compressed));
        static::assertTrue($reader->reachedEndOfDataSource());
    }

    public function testReadWithMaxBytesFromPipe(): void
    {
        [$read, $write] = IO\pipe();
        $write->writeAll('pipe data');
        $write->close();

        $reader = new CompressingReadHandle($read, new BrotliCompressor());
        $first = $reader->read(3);

        static::assertSame(3, strlen($first));

        $rest = $reader->readAll();
        static::assertSame('pipe data', brotli_uncompress($first . $rest));
    }

    public function testSmallChunkSize(): void
    {
        $original = str_repeat('chunk test data ', 100);
        $reader = new CompressingReadHandle(new IO\MemoryHandle($original), new BrotliCompressor(), chunkSize: 16);

        $compressed = $reader->readAll();

        static::assertSame($original, brotli_uncompress($compressed));
    }

    public function testLargeChunkSize(): void
    {
        $original = str_repeat('chunk test data ', 100);
        $reader = new CompressingReadHandle(new IO\MemoryHandle($original), new BrotliCompressor(), chunkSize: 65_536);

        $compressed = $reader->readAll();

        static::assertSame($original, brotli_uncompress($compressed));
    }

    public function testChunkSizeOneByteAtATime(): void
    {
        $original = 'hello world';
        $reader = new CompressingReadHandle(new IO\MemoryHandle($original), new BrotliCompressor(), chunkSize: 1);

        $compressed = $reader->readAll();

        static::assertSame($original, brotli_uncompress($compressed));
    }

    public function testChunkSizeIsPassedToInnerRead(): void
    {
        $called = false;
        $inner = $this->createStub(IO\ReadHandleInterface::class);
        $inner
            ->method('read')
            ->willReturnCallback(static function (null|int $maxBytes) use (&$called): string {
                static::assertSame(256, $maxBytes);
                if (!$called) {
                    $called = true;
                    return 'hello world';
                }

                return '';
            });
        $inner->method('reachedEndOfDataSource')->willReturnCallback(static fn(): bool => $called);

        $reader = new CompressingReadHandle($inner, new BrotliCompressor(), chunkSize: 256);
        $reader->read();

        static::assertTrue($called);
    }

    public function testChunkSizeIsPassedToInnerTryRead(): void
    {
        $called = false;
        $inner = $this->createStub(IO\ReadHandleInterface::class);
        $inner
            ->method('tryRead')
            ->willReturnCallback(static function (null|int $maxBytes) use (&$called): string {
                static::assertSame(512, $maxBytes);
                if (!$called) {
                    $called = true;
                    return 'hello world';
                }

                return '';
            });
        $inner->method('reachedEndOfDataSource')->willReturnCallback(static fn(): bool => $called);

        $reader = new CompressingReadHandle($inner, new BrotliCompressor(), chunkSize: 512);
        $reader->tryRead();

        static::assertTrue($called);
    }

    public function testDefaultChunkSizeIs8192(): void
    {
        $called = false;
        $inner = $this->createStub(IO\ReadHandleInterface::class);
        $inner
            ->method('read')
            ->willReturnCallback(static function (null|int $maxBytes) use (&$called): string {
                static::assertSame(8192, $maxBytes);
                if (!$called) {
                    $called = true;
                    return 'hello world';
                }

                return '';
            });
        $inner->method('reachedEndOfDataSource')->willReturnCallback(static fn(): bool => $called);

        $reader = new CompressingReadHandle($inner, new BrotliCompressor());
        $reader->read();

        static::assertTrue($called);
    }

    private function compress(string $data): string
    {
        $reader = new CompressingReadHandle(new IO\MemoryHandle($data), new BrotliCompressor());

        return $reader->readAll();
    }
}
