<?php

declare(strict_types=1);

namespace Psl\IO\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\IO;

final class FixedLengthReadHandleTest extends TestCase
{
    public function testReadExactlyExpectedLength(): void
    {
        $inner = new IO\IterableReadHandle(['hello']);
        $handle = new IO\FixedLengthReadHandle($inner, 5);

        static::assertSame('hello', $handle->read());
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadPrematureEofThrowsRuntimeException(): void
    {
        $inner = new IO\IterableReadHandle(['hi']);
        $handle = new IO\FixedLengthReadHandle($inner, 10);

        static::assertSame('hi', $handle->read());

        $this->expectException(IO\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Expected 10 bytes, but only 2 were available (premature EOF)');

        $handle->read();
    }

    public function testTryReadPrematureEofThrowsWhenUnderlyingAtEof(): void
    {
        $inner = new IO\IterableReadHandle(['ab']);
        $handle = new IO\FixedLengthReadHandle($inner, 5);

        static::assertSame('ab', $handle->read());

        static::assertSame('', $inner->read());
        static::assertTrue($inner->reachedEndOfDataSource());

        $this->expectException(IO\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Expected 5 bytes, but only 2 were available (premature EOF)');

        $handle->tryRead();
    }

    public function testTryReadReturnsEmptyWhenUnderlyingNotReady(): void
    {
        $inner = new IO\IterableReadHandle(['hello world']);
        $handle = new IO\FixedLengthReadHandle($inner, 5);

        static::assertSame('', $handle->tryRead());
        static::assertFalse($handle->reachedEndOfDataSource());
    }

    public function testReachedEndOfDataSourceTrueAfterConsumingAllBytes(): void
    {
        $inner = new IO\IterableReadHandle(['abcde']);
        $handle = new IO\FixedLengthReadHandle($inner, 5);

        $handle->read();

        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReachedEndOfDataSourceFalseBeforeConsumingAllBytes(): void
    {
        $inner = new IO\IterableReadHandle(['abcdefghij']);
        $handle = new IO\FixedLengthReadHandle($inner, 10);

        static::assertFalse($handle->reachedEndOfDataSource());

        $handle->read(3);

        static::assertFalse($handle->reachedEndOfDataSource());
    }

    public function testMaxBytesIsCappedToRemaining(): void
    {
        $inner = new IO\IterableReadHandle(['abcdefghij']);
        $handle = new IO\FixedLengthReadHandle($inner, 5);

        $data = $handle->read(100);

        static::assertSame('abcde', $data);
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testCloseClosesUnderlyingCloseHandle(): void
    {
        $inner = new IO\IterableReadHandle(['data']);
        $handle = new IO\FixedLengthReadHandle($inner, 4);

        static::assertFalse($handle->isClosed());
        static::assertFalse($inner->isClosed());

        $handle->close();

        static::assertTrue($handle->isClosed());
        static::assertTrue($inner->isClosed());
    }

    public function testCloseDoesNotFailWhenUnderlyingIsNotCloseable(): void
    {
        $inner = new class implements IO\ReadHandleInterface {
            use IO\ReadHandleConvenienceMethodsTrait;

            public function reachedEndOfDataSource(): bool
            {
                return true;
            }

            public function tryRead(null|int $maxBytes = null): string
            {
                return '';
            }

            public function read(
                null|int $maxBytes = null,
                \Psl\Async\CancellationTokenInterface $cancellation = new \Psl\Async\NullCancellationToken(),
            ): string {
                return '';
            }
        };

        $handle = new IO\FixedLengthReadHandle($inner, 0);
        $handle->close();

        static::assertTrue($handle->isClosed());
    }

    /**
     * @param (Closure(IO\FixedLengthReadHandle): mixed) $operation
     */
    #[DataProvider('provideOperations')]
    public function testAllOperationsThrowAfterClose(Closure $operation): void
    {
        $inner = new IO\IterableReadHandle(['hello']);
        $handle = new IO\FixedLengthReadHandle($inner, 5);
        $handle->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        $operation($handle);
    }

    /**
     * @return iterable<string, array{(Closure(IO\FixedLengthReadHandle): mixed)}>
     */
    public static function provideOperations(): iterable
    {
        yield 'read' => [static fn(IO\FixedLengthReadHandle $h) => $h->read()];
        yield 'readAll' => [static fn(IO\FixedLengthReadHandle $h) => $h->readAll()];
        yield 'readFixedSize' => [static fn(IO\FixedLengthReadHandle $h) => $h->readFixedSize(1)];
        yield 'tryRead' => [static fn(IO\FixedLengthReadHandle $h) => $h->tryRead()];
        yield 'reachedEndOfDataSource' => [static fn(IO\FixedLengthReadHandle $h) => $h->reachedEndOfDataSource()];
    }

    public function testZeroLengthImmediatelyReportsEof(): void
    {
        $inner = new IO\IterableReadHandle(['data']);
        $handle = new IO\FixedLengthReadHandle($inner, 0);

        static::assertTrue($handle->reachedEndOfDataSource());
        static::assertSame('', $handle->read());
        static::assertSame('', $handle->tryRead());
    }

    public function testUnderlyingHasMoreDataButStopsAtLength(): void
    {
        $inner = new IO\IterableReadHandle(['hello world, this is a long string']);
        $handle = new IO\FixedLengthReadHandle($inner, 5);

        static::assertSame('hello', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
        static::assertSame('', $handle->read());
    }

    public function testReadInMultipleChunks(): void
    {
        $inner = new IO\IterableReadHandle(['abc', 'def', 'ghi']);
        $handle = new IO\FixedLengthReadHandle($inner, 9);

        static::assertSame('abc', $handle->read());
        static::assertFalse($handle->reachedEndOfDataSource());
        static::assertSame('def', $handle->read());
        static::assertFalse($handle->reachedEndOfDataSource());
        static::assertSame('ghi', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadWithSmallMaxBytes(): void
    {
        $inner = new IO\IterableReadHandle(['abcdef']);
        $handle = new IO\FixedLengthReadHandle($inner, 6);

        static::assertSame('ab', $handle->read(2));
        static::assertSame('cd', $handle->read(2));
        static::assertSame('ef', $handle->read(2));
        static::assertTrue($handle->reachedEndOfDataSource());
    }
}
