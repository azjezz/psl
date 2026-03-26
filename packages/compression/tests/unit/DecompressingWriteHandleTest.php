<?php

declare(strict_types=1);

namespace Psl\Compression\Tests\Unit;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Psl\Compression\CompressingReadHandle;
use Psl\Compression\DecompressingWriteHandle;
use Psl\Compression\Tests\Fixture\BrotliCompressor;
use Psl\Compression\Tests\Fixture\BrotliDecompressor;
use Psl\Compression\Tests\Fixture\NullDecompressor;
use Psl\Compression\Tests\Fixture\PartialWriteHandle;
use Psl\IO;

use function str_repeat;
use function strlen;

#[RequiresPhpExtension('brotli')]
final class DecompressingWriteHandleTest extends TestCase
{
    public function testWrite(): void
    {
        $compressed = $this->compress('hello');

        $output = new IO\MemoryHandle();
        $writer = new DecompressingWriteHandle($output, new BrotliDecompressor());

        $written = $writer->write($compressed);

        static::assertSame(strlen($compressed), $written);
    }

    public function testWriteAndFlush(): void
    {
        $compressed = $this->compress('hello world');

        $output = new IO\MemoryHandle();
        $writer = new DecompressingWriteHandle($output, new BrotliDecompressor());

        $writer->write($compressed);
        $writer->flush();

        $output->seek(0);

        static::assertSame('hello world', $output->readAll());
    }

    public function testWriteAll(): void
    {
        $compressed = $this->compress('hello world');

        $output = new IO\MemoryHandle();
        $writer = new DecompressingWriteHandle($output, new BrotliDecompressor());

        $writer->writeAll($compressed);
        $writer->flush();

        $output->seek(0);

        static::assertSame('hello world', $output->readAll());
    }

    public function testTryWrite(): void
    {
        $compressed = $this->compress('data');

        $output = new IO\MemoryHandle();
        $writer = new DecompressingWriteHandle($output, new BrotliDecompressor());

        $written = $writer->tryWrite($compressed);

        static::assertSame(strlen($compressed), $written);
    }

    public function testTryWriteAndFlush(): void
    {
        $compressed = $this->compress('hello');

        $output = new IO\MemoryHandle();
        $writer = new DecompressingWriteHandle($output, new BrotliDecompressor());

        $writer->tryWrite($compressed);
        $writer->flush();

        $output->seek(0);

        static::assertSame('hello', $output->readAll());
    }

    public function testWriteReturnsCompressedInputLength(): void
    {
        $compressed = $this->compress('hello');

        $output = new IO\MemoryHandle();
        $writer = new DecompressingWriteHandle($output, new BrotliDecompressor());

        $written = $writer->write($compressed);

        static::assertSame(strlen($compressed), $written);
    }

    public function testLargeData(): void
    {
        $original = str_repeat('The quick brown fox jumps over the lazy dog. ', 1000);
        $compressed = $this->compress($original);

        $output = new IO\MemoryHandle();
        $writer = new DecompressingWriteHandle($output, new BrotliDecompressor());

        $writer->writeAll($compressed);
        $writer->flush();

        $output->seek(0);

        static::assertSame($original, $output->readAll());
    }

    public function testReaderToWriterRoundtrip(): void
    {
        $original = "line one\nline two\nline three\n";
        $compressed = $this->compress($original);

        $output = new IO\MemoryHandle();
        $writer = new DecompressingWriteHandle($output, new BrotliDecompressor());
        $writer->writeAll($compressed);
        $writer->flush();

        $output->seek(0);

        static::assertSame($original, $output->readAll());
    }

    public function testDrainTryWithEmptyBuffer(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new DecompressingWriteHandle($output, new NullDecompressor());

        $written = $writer->tryWrite('');

        static::assertSame(0, $written);
        $output->seek(0);
        static::assertSame('', $output->readAll());
    }

    public function testDrainTryWithNonEmptyBuffer(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new DecompressingWriteHandle($output, new NullDecompressor());

        $writer->tryWrite('hello');

        $output->seek(0);

        static::assertSame('hello', $output->readAll());
    }

    public function testDrainTryWithPartialInnerWrite(): void
    {
        $output = new PartialWriteHandle(maxBytesPerWrite: 3);
        $writer = new DecompressingWriteHandle($output, new NullDecompressor());

        $writer->tryWrite('hello');

        static::assertSame('hel', $output->getBuffer());
    }

    public function testDrainWithEmptyBuffer(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new DecompressingWriteHandle($output, new NullDecompressor());

        $written = $writer->write('');

        static::assertSame(0, $written);
        $output->seek(0);
        static::assertSame('', $output->readAll());
    }

    public function testDrainWithNonEmptyBuffer(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new DecompressingWriteHandle($output, new NullDecompressor());

        $writer->write('hello');

        $output->seek(0);

        static::assertSame('hello', $output->readAll());
    }

    public function testDrainWithPartialInnerWrite(): void
    {
        $output = new PartialWriteHandle(maxBytesPerWrite: 3);
        $writer = new DecompressingWriteHandle($output, new NullDecompressor());

        $writer->write('hello');

        static::assertSame('hel', $output->getBuffer());
    }

    public function testDrainAllWithNonEmptyBuffer(): void
    {
        $output = new PartialWriteHandle(maxBytesPerWrite: 2);
        $writer = new DecompressingWriteHandle($output, new NullDecompressor());

        $writer->tryWrite('hello world');
        $writer->flush();

        static::assertSame('hello world', $output->getBuffer());
    }

    public function testDrainAllWithEmptyBuffer(): void
    {
        $output = new IO\MemoryHandle();
        $writer = new DecompressingWriteHandle($output, new NullDecompressor());

        $writer->flush();

        $output->seek(0);

        static::assertSame('', $output->readAll());
    }

    private function compress(string $data): string
    {
        $reader = new CompressingReadHandle(new IO\MemoryHandle($data), new BrotliCompressor());

        return $reader->readAll();
    }
}
