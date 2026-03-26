<?php

declare(strict_types=1);

namespace Psl\Compression\Tests\Unit;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Psl\Compression\CompressingWriteHandle;
use Psl\Compression\Tests\Fixture\BrotliCompressor;
use Psl\Compression\Tests\Fixture\NullCompressor;
use Psl\Compression\Tests\Fixture\PartialWriteHandle;
use Psl\IO;

use function brotli_uncompress;
use function str_repeat;
use function strlen;

#[RequiresPhpExtension('brotli')]
final class CompressingWriteHandleTest extends TestCase
{
    public function testWrite(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new CompressingWriteHandle($output, new BrotliCompressor());

        $written = $writer->write('hello');

        static::assertSame(5, $written);
    }

    public function testWriteAndFlush(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new CompressingWriteHandle($output, new BrotliCompressor());

        $writer->write('hello world');
        $writer->flush();

        $output->seek(0);
        $compressed = $output->readAll();

        static::assertSame('hello world', brotli_uncompress($compressed));
    }

    public function testWriteAll(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new CompressingWriteHandle($output, new BrotliCompressor());

        $writer->writeAll('hello world');
        $writer->flush();

        $output->seek(0);

        static::assertSame('hello world', brotli_uncompress($output->readAll()));
    }

    public function testTryWrite(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new CompressingWriteHandle($output, new BrotliCompressor());

        $written = $writer->tryWrite('data');

        static::assertSame(4, $written);
    }

    public function testTryWriteAndFlush(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new CompressingWriteHandle($output, new BrotliCompressor());

        $writer->tryWrite('hello');
        $writer->flush();

        $output->seek(0);

        static::assertSame('hello', brotli_uncompress($output->readAll()));
    }

    public function testMultipleWritesThenFlush(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new CompressingWriteHandle($output, new BrotliCompressor());

        $writer->write('hello');
        $writer->write(' ');
        $writer->write('world');
        $writer->flush();

        $output->seek(0);

        static::assertSame('hello world', brotli_uncompress($output->readAll()));
    }

    public function testWriteReturnsInputLength(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new CompressingWriteHandle($output, new BrotliCompressor());

        static::assertSame(0, $writer->write(''));
        static::assertSame(5, $writer->write('hello'));
        static::assertSame(11, $writer->write('hello world'));
    }

    public function testLargeData(): void
    {
        $original = str_repeat('The quick brown fox jumps over the lazy dog. ', 1000);

        $output = new IO\MemoryHandle();
        $writer = new CompressingWriteHandle($output, new BrotliCompressor());

        $writer->writeAll($original);
        $writer->flush();

        $output->seek(0);
        $compressed = $output->readAll();

        static::assertLessThan(strlen($original), strlen($compressed));
        static::assertSame($original, brotli_uncompress($compressed));
    }

    public function testDrainTryWritesBufferToInnerHandle(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new CompressingWriteHandle($output, new NullCompressor());

        $writer->tryWrite('hello');

        $output->seek(0);

        static::assertSame('hello', $output->readAll());
    }

    public function testDrainTryWithPartialInnerWrite(): void
    {
        $output = new PartialWriteHandle(maxBytesPerWrite: 3);
        $writer = new CompressingWriteHandle($output, new NullCompressor());

        $writer->tryWrite('hello');

        static::assertSame('hel', $output->getBuffer());
    }

    public function testDrainWritesBufferToInnerHandle(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new CompressingWriteHandle($output, new NullCompressor());

        $writer->write('hello');

        $output->seek(0);

        static::assertSame('hello', $output->readAll());
    }

    public function testDrainWithPartialInnerWrite(): void
    {
        $output = new PartialWriteHandle(maxBytesPerWrite: 3);
        $writer = new CompressingWriteHandle($output, new NullCompressor());

        $writer->write('hello');

        static::assertSame('hel', $output->getBuffer());
    }

    public function testDrainAllWithEmptyBuffer(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new CompressingWriteHandle($output, new NullCompressor());

        $writer->flush();

        $output->seek(0);

        static::assertSame('', $output->readAll());
    }

    public function testDrainAllFlushesEntireBuffer(): void
    {
        $output = new PartialWriteHandle(maxBytesPerWrite: 2);
        $writer = new CompressingWriteHandle($output, new NullCompressor());

        $writer->tryWrite('hello world');
        $writer->flush();

        static::assertSame('hello world', $output->getBuffer());
    }
}
