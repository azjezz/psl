<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\IO;
use Psl\SMTP\Internal\DotStuffingReadHandle;

use function str_repeat;

final class DotStuffingReadHandleTest extends TestCase
{
    public function testEmptyMessage(): void
    {
        $inner = new IO\MemoryHandle('');
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame(".\r\n", $result);
    }

    public function testSimpleMessage(): void
    {
        $inner = new IO\MemoryHandle("Hello World\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("Hello World\r\n.\r\n", $result);
    }

    public function testDotAtLineStart(): void
    {
        $inner = new IO\MemoryHandle("Hello\r\n.Hidden\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("Hello\r\n..Hidden\r\n.\r\n", $result);
    }

    public function testMultipleDots(): void
    {
        $inner = new IO\MemoryHandle(".First\r\n..Second\r\nThird\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("..First\r\n...Second\r\nThird\r\n.\r\n", $result);
    }

    public function testMessageWithoutTrailingCRLF(): void
    {
        $inner = new IO\MemoryHandle('No trailing newline');
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("No trailing newline\r\n.\r\n", $result);
    }

    public function testDotOnlyLine(): void
    {
        $inner = new IO\MemoryHandle("Before\r\n.\r\nAfter\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("Before\r\n..\r\nAfter\r\n.\r\n", $result);
    }

    public function testReachedEndOfDataSource(): void
    {
        $inner = new IO\MemoryHandle("test\r\n");
        $handle = new DotStuffingReadHandle($inner);

        static::assertFalse($handle->reachedEndOfDataSource());

        $handle->readAll();

        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testDotAtVeryStart(): void
    {
        $inner = new IO\MemoryHandle(".starts with dot\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("..starts with dot\r\n.\r\n", $result);
    }

    public function testDoubleDotOnLine(): void
    {
        $inner = new IO\MemoryHandle("Hello\r\n..already double\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("Hello\r\n...already double\r\n.\r\n", $result);
    }

    public function testMultipleConsecutiveDotLines(): void
    {
        $inner = new IO\MemoryHandle(".\r\n.\r\n.\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("..\r\n..\r\n..\r\n.\r\n", $result);
    }

    public function testVeryLongLine(): void
    {
        $longLine = str_repeat('A', 10_000) . "\r\n";
        $inner = new IO\MemoryHandle($longLine);
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame($longLine . ".\r\n", $result);
    }

    public function testInputEndingWithCRLF(): void
    {
        $inner = new IO\MemoryHandle("test\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("test\r\n.\r\n", $result);
    }

    public function testInputEndingWithJustLF(): void
    {
        $inner = new IO\MemoryHandle("test\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        // Ends with \n so atLineStart is true, no extra \r\n needed
        static::assertSame("test\n.\r\n", $result);
    }

    public function testInputEndingWithoutNewline(): void
    {
        $inner = new IO\MemoryHandle('no newline at end');
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("no newline at end\r\n.\r\n", $result);
    }

    public function testSmallReadCalls(): void
    {
        $inner = new IO\MemoryHandle("Hello\r\n.World\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = '';
        while (!$handle->reachedEndOfDataSource()) {
            $chunk = $handle->tryRead(1);
            $result .= $chunk;
        }

        static::assertSame("Hello\r\n..World\r\n.\r\n", $result);
    }

    public function testReadWithMaxBytes(): void
    {
        $inner = new IO\MemoryHandle("Hello World\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $first = $handle->tryRead(5);
        static::assertSame('Hello', $first);

        $rest = $handle->readAll();
        static::assertSame(" World\r\n.\r\n", $rest);
    }

    public function testReadAfterFinished(): void
    {
        $inner = new IO\MemoryHandle('test');
        $handle = new DotStuffingReadHandle($inner);

        $handle->readAll();
        static::assertTrue($handle->reachedEndOfDataSource());

        static::assertSame('', $handle->tryRead());
        static::assertSame('', $handle->read());
    }

    public function testDotInMiddleOfLine(): void
    {
        $inner = new IO\MemoryHandle("Hello.World\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("Hello.World\r\n.\r\n", $result);
    }

    public function testDotAfterLF(): void
    {
        $inner = new IO\MemoryHandle("Line1\n.Line2\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        // After \n, atLineStart is true
        static::assertSame("Line1\n..Line2\r\n.\r\n", $result);
    }

    public function testOnlyDots(): void
    {
        $inner = new IO\MemoryHandle(".\n.\n.\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("..\n..\n..\n.\r\n", $result);
    }

    public function testBinaryLikeContent(): void
    {
        $inner = new IO\MemoryHandle("\x00\x01\x02\r\n.\x03\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("\x00\x01\x02\r\n..\x03\r\n.\r\n", $result);
    }

    public function testOnlyCRLF(): void
    {
        $inner = new IO\MemoryHandle("\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("\r\n.\r\n", $result);
    }

    public function testMultipleCRLFs(): void
    {
        $inner = new IO\MemoryHandle("\r\n\r\n\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("\r\n\r\n\r\n.\r\n", $result);
    }

    public function testDotFollowedByCRLF(): void
    {
        $inner = new IO\MemoryHandle(".\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame("..\r\n.\r\n", $result);
    }

    public function testReachedEndOfDataSourceInitiallyFalse(): void
    {
        $inner = new IO\MemoryHandle('data');
        $handle = new DotStuffingReadHandle($inner);

        static::assertFalse($handle->reachedEndOfDataSource());
    }

    public function testReachedEndOfDataSourceAfterFullRead(): void
    {
        $inner = new IO\MemoryHandle("data\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $handle->readAll();

        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadMethodWithCancellation(): void
    {
        $inner = new IO\MemoryHandle("Hello\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->read();

        static::assertNotSame('', $result);
    }

    public function testTryReadVsRead(): void
    {
        $inner1 = new IO\MemoryHandle("test\r\n");
        $handle1 = new DotStuffingReadHandle($inner1);
        $result1 = $handle1->readAll();

        $inner2 = new IO\MemoryHandle("test\r\n");
        $handle2 = new DotStuffingReadHandle($inner2);

        $result2 = '';
        while (!$handle2->reachedEndOfDataSource()) {
            $chunk = $handle2->read();
            $result2 .= $chunk;
        }

        static::assertSame($result1, $result2);
    }

    public function testLargeInputExceedingChunkSize(): void
    {
        $line = str_repeat('X', 9000) . "\r\n";
        $inner = new IO\MemoryHandle($line);
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertSame($line . ".\r\n", $result);
    }

    public function testDotAtExactChunkBoundary(): void
    {
        $prefix = str_repeat('A', 8190) . "\n";
        $inner = new IO\MemoryHandle($prefix . ".stuffme\r\n");
        $handle = new DotStuffingReadHandle($inner);

        $result = $handle->readAll();

        static::assertStringContainsString('..stuffme', $result);
    }
}
