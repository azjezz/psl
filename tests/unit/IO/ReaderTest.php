<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\IO;

use PHPUnit\Framework\TestCase;
use Psl\File;
use Psl\IO;

final class ReaderTest extends TestCase
{
    public function testReadByteOnAnEmptyBufferFillsTheInternalBufferAndMarksTheReaderAsEOF(): void
    {
        $handle = new IO\MemoryHandle('a');
        $reader = new IO\Reader($handle);

        static::assertSame('a', $reader->readByte());
        static::assertTrue($reader->reachedEndOfDataSource());
    }

    public function testReadByteOnAnEmptyBufferThrows(): void
    {
        $handle = new IO\MemoryHandle();
        $reader = new IO\Reader($handle);

        $this->expectException(IO\Exception\RuntimeException::class);

        $reader->readByte();
    }

    public function testReadEmptyHandle(): void
    {
        $handle = new IO\MemoryHandle();
        $reader = new IO\Reader($handle);

        static::assertEmpty($reader->tryRead());
        static::assertTrue($reader->reachedEndOfDataSource());
    }

    public function testReadingFile(): void
    {
        $handle = File\open_read_only(__FILE__);
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
        static::assertSame('File;', $reader->readLine());
        static::assertSame('use Psl\IO;', $reader->readLine());
        static::assertSame('', $reader->readLine());
        static::assertSame('final class', $reader->readFixedSize(11));

        $handle->readAll();

        static::assertEmpty($handle->read());

        /**
         * Handle has reached EOL, but the buffer still contains content.
         */
        static::assertFalse($reader->reachedEndOfDataSource());
        static::assertSame(' ', $reader->readByte());
        static::assertSame('ReaderTest', $reader->readFixedSize(10));
    }

    public function testReadEof(): void
    {
        $handle = new IO\MemoryHandle('hello');
        $reader = new IO\Reader($handle);
        static::assertSame('hello', $reader->read());
        static::assertSame('', $reader->read());
        static::assertTrue($reader->reachedEndOfDataSource());
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
        static::assertTrue($reader->reachedEndOfDataSource());
        static::assertSame('', $reader->read());
    }

    public function testReadUntilBoundedFindsPrefix(): void
    {
        $handle = new IO\MemoryHandle('hello\r\nworld');
        $reader = new IO\Reader($handle);

        static::assertSame('hello', $reader->readUntilBounded('\r\n', 100));
        static::assertSame('world', $reader->read());
    }

    public function testReadUntilBoundedReturnsNullOnEof(): void
    {
        $handle = new IO\MemoryHandle('hello');
        $reader = new IO\Reader($handle);

        static::assertNull($reader->readUntilBounded('@', 100));
    }

    public function testReadUntilBoundedThrowsOverflowWhenBufferExceedsMaxBeforeRead(): void
    {
        $handle = new IO\MemoryHandle('aaaaaaaaaa\r\n');
        $reader = new IO\Reader($handle);

        $this->expectException(IO\Exception\OverflowException::class);
        $this->expectExceptionMessage('Exceeded maximum byte limit (5) before encountering the suffix ("\r\n").');

        $reader->readUntilBounded('\r\n', 5);
    }

    public function testReadUntilBoundedThrowsOverflowWhenSuffixFoundBeyondMax(): void
    {
        $handle = new IO\MemoryHandle('abcdefghij:end');
        $reader = new IO\Reader($handle);

        $this->expectException(IO\Exception\OverflowException::class);
        $this->expectExceptionMessage('Exceeded maximum byte limit (3) before encountering the suffix (":end").');

        $reader->readUntilBounded(':end', 3);
    }

    public function testReadUntilBoundedExactMaxBytes(): void
    {
        $handle = new IO\MemoryHandle('abcde:end');
        $reader = new IO\Reader($handle);

        static::assertSame('abcde', $reader->readUntilBounded(':end', 5));
    }

    public function testReadUntilBoundedSuffixAtStart(): void
    {
        $handle = new IO\MemoryHandle(':endrest');
        $reader = new IO\Reader($handle);

        static::assertSame('', $reader->readUntilBounded(':end', 10));
        static::assertSame('rest', $reader->read());
    }

    public function testReadUntilBoundedMultipleSuffixes(): void
    {
        $handle = new IO\MemoryHandle('ab|cd|ef');
        $reader = new IO\Reader($handle);

        static::assertSame('ab', $reader->readUntilBounded('|', 100));
        static::assertSame('cd', $reader->readUntilBounded('|', 100));
        static::assertNull($reader->readUntilBounded('|', 100));
    }

    public function testReadUntilBoundedPreservesBufferAfterOverflow(): void
    {
        $handle = new IO\MemoryHandle('toolongcontent:endfoo');
        $reader = new IO\Reader($handle);

        try {
            $reader->readUntilBounded(':end', 3);
            static::fail('Expected OverflowException');
        } catch (IO\Exception\OverflowException) {
            static::addToAssertionCount(1);
        }

        static::assertSame('toolongcontent:endfoo', $reader->read());
    }

    public function testReadUntilBoundedSingleByteSuffix(): void
    {
        $handle = new IO\MemoryHandle('abc\ndef');
        $reader = new IO\Reader($handle);

        static::assertSame('abc', $reader->readUntilBounded('\n', 10));
        static::assertSame('def', $reader->read());
    }

    public function testReadUntilBoundedEmptyHandle(): void
    {
        $handle = new IO\MemoryHandle('');
        $reader = new IO\Reader($handle);

        static::assertNull($reader->readUntilBounded(':end', 100));
    }

    public function testReadUntilBoundedMaxBytesExactlyAtSuffix(): void
    {
        $handle = new IO\MemoryHandle('abc|rest');
        $reader = new IO\Reader($handle);

        static::assertSame('abc', $reader->readUntilBounded('|', 3));
        static::assertSame('rest', $reader->read());
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

        static::assertTrue($reader->reachedEndOfDataSource());
    }

    public function testIsEndOfLineWithEmptyHandle(): void
    {
        $handle = new IO\MemoryHandle();
        $reader = new IO\Reader($handle);

        static::assertTrue($reader->reachedEndOfDataSource());
        static::assertTrue($reader->reachedEndOfDataSource());
    }

    public function testIsEndOfLineWithNonEmptyHandle(): void
    {
        $handle = new IO\MemoryHandle('hello');
        $reader = new IO\Reader($handle);

        static::assertFalse($reader->reachedEndOfDataSource());
        static::assertSame('hello', $reader->readLine());
    }
}
