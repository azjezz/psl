<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Encoding\Hex;

use PHPUnit\Framework\TestCase;
use Psl\Encoding\Exception;
use Psl\Encoding\Hex;
use Psl\IO;

final class WriteHandleTest extends TestCase
{
    public function testDecodingWriteRemainderConcatOrder(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Hex\DecodingWriteHandle($inner);
        $handle->write('4');
        $handle->write('8');
        $handle->flush();
        $inner->seek(0);
        static::assertSame('H', $inner->readAll());
    }

    public function testDecodingWriteModulusHandling(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Hex\DecodingWriteHandle($inner);
        $handle->write('486');
        $inner->seek(0);
        static::assertSame('H', $inner->readAll());
        $handle->write('5');
        $handle->flush();
        $inner->seek(0);
        static::assertSame('He', $inner->readAll());
    }

    public function testDecodingWriteSingleCharNoOutput(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Hex\DecodingWriteHandle($inner);
        $handle->write('4');
        $inner->seek(0);
        static::assertSame('', $inner->readAll());
    }

    public function testDecodingWriteFlushEmpty(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Hex\DecodingWriteHandle($inner);
        $handle->flush();
        $inner->seek(0);
        static::assertSame('', $inner->readAll());
    }

    public function testDecodingWriteOddFlushThrows(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Hex\DecodingWriteHandle($inner);
        $handle->write('a');
        $this->expectException(Exception\RangeException::class);
        $handle->flush();
    }

    public function testEncodingWriteChunkedInput(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Hex\EncodingWriteHandle($inner);
        $handle->write('A');
        $handle->write('B');
        $inner->seek(0);
        static::assertSame('4142', $inner->readAll());
    }

    public function testEncodingWriteEmptyWrite(): void
    {
        $inner = new IO\MemoryHandle();
        $handle = new Hex\EncodingWriteHandle($inner);
        $handle->write('');
        $inner->seek(0);
        static::assertSame('', $inner->readAll());
    }
}
