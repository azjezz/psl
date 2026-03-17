<?php

declare(strict_types=1);

namespace Psl\Encoding\Tests\Unit\QuotedPrintable;

use PHPUnit\Framework\TestCase;
use Psl\Encoding\QuotedPrintable;
use Psl\IO;

final class DecodingReadHandleTest extends TestCase
{
    public function testTryReadBufferNonEmptyNotEofDoesNotRefill(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('ABCDEF'));
        $first = $handle->tryRead(2);
        static::assertSame('AB', $first);
        $second = $handle->tryRead(2);
        static::assertSame('CD', $second);
    }

    public function testTryReadEmptyHandleReturnsEmptyString(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle(''));
        static::assertSame('', $handle->tryRead());
    }

    public function testTryReadMaxBytesExactlyEqualsBuffer(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('XYZ'));
        $result = $handle->tryRead(3);
        static::assertSame('XYZ', $result);
        static::assertSame('', $handle->tryRead());
    }

    public function testTryReadNullMaxBytesReturnsAll(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('ABCDEF'));
        $result = $handle->tryRead(null);
        static::assertSame('ABCDEF', $result);
    }

    public function testTryReadMaxBytesLessThanBufferReturnsPartial(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('ABCDEF'));
        static::assertSame('A', $handle->tryRead(1));
        static::assertSame('BCDEF', $handle->tryRead());
    }

    public function testTryReadOnEofReturnsEmpty(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle(''));
        static::assertSame('', $handle->tryRead());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadAfterEofWithEmptyBufferReturnsEmptyString(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('A'));
        $handle->readAll();
        static::assertSame('', $handle->read());
    }

    public function testReadAfterEofReturnsEmptyNotNull(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('X'));
        $handle->readAll();
        $result = $handle->read();
        static::assertIsString($result);
        static::assertSame('', $result);
    }

    public function testReadEmptyHandleReturnsEmptyString(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle(''));
        static::assertSame('', $handle->read());
    }

    public function testReadMaxBytesExactlyEqualsBuffer(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('AB'));
        static::assertSame('AB', $handle->read(2));
    }

    public function testReadMaxBytesLessThanBuffer(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('ABCDEF'));
        static::assertSame('AB', $handle->read(2));
        static::assertSame('CDEF', $handle->read(100));
    }

    public function testReadMultipleSequentialReadsAfterEof(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('Z'));
        $handle->readAll();
        static::assertSame('', $handle->read());
        static::assertSame('', $handle->read());
        static::assertSame('', $handle->read());
    }

    public function testReadMaxBytesLargerThanBuffer(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('AB'));
        static::assertSame('AB', $handle->read(100));
    }

    public function testReadNullMaxBytesReturnsAll(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('Hello=20World'));
        static::assertSame('Hello World', $handle->read(null));
    }

    public function testReadByteSingleByteHandle(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('X'));
        static::assertSame('X', $handle->readByte());
    }

    public function testReadByteSecondIsDifferent(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('AB'));
        static::assertSame('A', $handle->readByte());
        static::assertSame('B', $handle->readByte());
    }

    public function testReadByteEmptyHandleThrows(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle(''));
        $this->expectException(IO\Exception\RuntimeException::class);
        $handle->readByte();
    }

    public function testReadByteMultiByteBufferThenReadByte(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('ABCDE'));
        static::assertSame('A', $handle->readByte());
        static::assertSame('B', $handle->readByte());
        static::assertSame('CDE', $handle->readAll());
    }

    public function testReadByteAfterEofThrows(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('A'));
        $handle->readAll();
        $this->expectException(IO\Exception\RuntimeException::class);
        $handle->readByte();
    }

    public function testReadByteEncodedSequence(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('=41=42'));
        static::assertSame('A', $handle->readByte());
        static::assertSame('B', $handle->readByte());
    }

    public function testReadLineWithCRLFStripsCR(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle("hello\r\nworld"));
        static::assertSame('hello', $handle->readLine());
        static::assertSame('world', $handle->readLine());
    }

    public function testReadLineWithoutNewlineReturnsContentThenNull(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('no newline'));
        static::assertSame('no newline', $handle->readLine());
        static::assertNull($handle->readLine());
    }

    public function testReadLineOnEmptyReturnsNull(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle(''));
        static::assertNull($handle->readLine());
    }

    public function testReadLineAfterPartialRead(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle("first\r\nsecond"));
        static::assertSame('first', $handle->readLine());
        static::assertSame('second', $handle->readAll());
    }

    public function testReadUntilSuffixFoundImmediately(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('key=3Dvalue'));
        static::assertNull($handle->readUntil('|'));
    }

    public function testReadUntilSuffixFound(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('key=3Dvalue&other=3Ddata'));
        static::assertSame('key=value', $handle->readUntil('&'));
    }

    public function testReadUntilSuffixNotFound(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('hello=20world'));
        static::assertNull($handle->readUntil('@'));
    }

    public function testReadUntilMultipleSuffixes(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('a|b|c'));
        static::assertSame('a', $handle->readUntil('|'));
        static::assertSame('b', $handle->readUntil('|'));
        static::assertNull($handle->readUntil('|'));
    }

    public function testReadUntilRemainingDataCorrect(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('abc|def'));
        static::assertSame('abc', $handle->readUntil('|'));
        static::assertSame('def', $handle->readAll());
    }

    public function testReadUntilBoundedSuffixFoundWithinLimit(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('short:end'));
        static::assertSame('short', $handle->readUntilBounded(':end', 10));
    }

    public function testReadUntilBoundedSuffixFoundBeyondLimitThrows(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('toolongcontent:end'));
        $this->expectException(IO\Exception\OverflowException::class);
        $handle->readUntilBounded(':end', 3);
    }

    public function testReadUntilBoundedNotFoundWithinLimit(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('nothing'));
        static::assertNull($handle->readUntilBounded('@', 100));
    }

    public function testReadUntilBoundedContentExactlyAtMaxBytes(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('abcde:s'));
        static::assertSame('abcde', $handle->readUntilBounded(':s', 5));
    }

    public function testReadUntilBoundedContentOneOverMaxBytesThrows(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('abcdef:s'));
        $this->expectException(IO\Exception\OverflowException::class);
        $handle->readUntilBounded(':s', 5);
    }

    public function testReadUntilBoundedRemainingDataCorrect(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('ab:endrest'));
        static::assertSame('ab', $handle->readUntilBounded(':end', 10));
        static::assertSame('rest', $handle->readAll());
    }

    public function testReadUntilBoundedBufferGrowsPastLimitThrows(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle(str_repeat('x', 100) . ':end'));
        $this->expectException(IO\Exception\OverflowException::class);
        $handle->readUntilBounded(':end', 10);
    }

    public function testReachedEndOfDataSourceBeforeAndAfterRead(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('A'));
        static::assertFalse($handle->reachedEndOfDataSource());
        $handle->readAll();
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testFillBufferSoftBreakContinuation(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle("Hello=\r\n World"));
        static::assertSame('Hello World', $handle->readAll());
    }

    public function testFillBufferHardBreakTracking(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle("line1\r\nline2"));
        static::assertSame("line1\r\nline2", $handle->readAll());
    }

    public function testFillBufferAccumulatedBuffer(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle("part=\r\none=\r\ntwo"));
        static::assertSame('partonetwo', $handle->readAll());
    }

    public function testFillBufferEofWithAccumulatedData(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('Hello=20World'));
        static::assertSame('Hello World', $handle->readAll());
    }

    public function testFillBufferEncodedChars(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('=41=42=43'));
        static::assertSame('ABC', $handle->readAll());
    }

    public function testFillBufferPlainText(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle('plain text'));
        static::assertSame('plain text', $handle->readAll());
    }

    public function testSoftBreakThenReadByte(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle("AB=\r\nCD"));
        static::assertSame('A', $handle->readByte());
        static::assertSame('B', $handle->readByte());
        static::assertSame('C', $handle->readByte());
        static::assertSame('D', $handle->readByte());
    }

    public function testInterleavedMethods(): void
    {
        $handle = new QuotedPrintable\DecodingReadHandle(new IO\MemoryHandle("abc|def\r\nghi"));
        static::assertSame('abc', $handle->readUntil('|'));
        static::assertSame('d', $handle->readByte());
        static::assertSame('ef', $handle->readUntil("\r\n"));
        static::assertSame('ghi', $handle->readAll());
    }
}
