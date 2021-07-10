<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\IO;

use PHPUnit\Framework\TestCase;
use Psl\IO;

final class ReaderTest extends TestCase
{
    public function testReadingFile(): void
    {
        $handle = IO\Internal\open(__FILE__, 'rb+');
        $reader = new IO\Reader($handle);

        static::assertSame($handle, $reader->getHandle());

        static::assertSame('<?php', $reader->readLine());
        static::assertSame('', $reader->readLine());
        static::assertSame('declare(strict_types=1);', $reader->readLine());
        static::assertSame('', $reader->readLine());
        static::assertSame('namespace Psl\\Tests\\Unit\\IO;', $reader->readLine());
        static::assertSame('', $reader->readLine());
        static::assertSame('use PHPUnit\\Framework\\TestCase;', $reader->readLine());

        static::assertSame('use Psl', $reader->readUntil('\\'));
        static::assertSame('IO;', $reader->readLine());
        static::assertSame('', $reader->readLine());
        static::assertSame('final class', $reader->readFixedSize(11));

        do {
            $eof = $handle->read() === '';
        } while (!$eof);

        static::assertEmpty($handle->read());

        /**
         * Handle has reached EOL, but the buffer still contains content.
         */
        static::assertFalse($reader->isEndOfFile());
        static::assertSame(' ', $reader->readByte());
        static::assertSame('ReaderTest', $reader->readFixedSize(10));
    }

    public function testReadEof(): void
    {
        $handle = new IO\MemoryHandle('hello');
        $reader = new IO\Reader($handle);
        static::assertSame('hello', $reader->read());
        static::assertSame('', $reader->read());
        static::assertTrue($reader->isEndOfFile());
        static::assertSame('', $reader->read());
    }

    public function testReadSome(): void
    {
        $handle = new IO\MemoryHandle('hello, world!');
        $reader = new IO\Reader($handle);

        static::assertSame('he', $reader->read(2));
        static::assertSame('ll', $reader->read(2));
        static::assertSame('o,', $reader->read(2));
        static::assertSame(' world!', $reader->read(10));
        static::assertTrue($reader->isEndOfFile());
        static::assertSame('', $reader->read());
    }

    public function testReadUntilInvalidSuffix(): void
    {
        $handle = new IO\MemoryHandle('hello');
        $reader = new IO\Reader($handle);

        static::assertNull($reader->readUntil('@'));
    }

    public function testReadLineEol(): void
    {
        $handle = new IO\MemoryHandle();
        $handle->write('hello');
        $reader = new IO\Reader($handle);

        static::assertNull($reader->readLine());
    }

    public function testReadLineNoNewLine(): void
    {
        $handle = new IO\MemoryHandle();
        $handle->write('hello, world!');
        $handle->seek(5);

        $reader = new IO\Reader($handle);

        static::assertSame(', world!', $reader->readLine());
        static::assertNull($reader->readLine());
    }

    public function testIsEndOfLineWithEofHandle(): void
    {
        $handle = new IO\MemoryHandle();
        $handle->write('hello, world!');

        $reader = new IO\Reader($handle);

        static::assertTrue($reader->isEndOfFile());
    }

    public function testIsEndOfLineWithEmptyHandle(): void
    {
        $handle = new IO\MemoryHandle();
        $reader = new IO\Reader($handle);

        static::assertTrue($reader->isEndOfFile());
        static::assertTrue($reader->isEndOfFile());
    }

    public function testIsEndOfLineWithNonEmptyHandle(): void
    {
        $handle = new IO\MemoryHandle('hello');
        $reader = new IO\Reader($handle);

        static::assertFalse($reader->isEndOfFile());
        static::assertSame('hello', $reader->readLine());
    }
}
