<?php

declare(strict_types=1);

namespace Psl\IO\Tests\Unit;

use Closure;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

final class ConcatReadHandleTest extends TestCase
{
    /**
     * @param (Closure(IO\ConcatReadHandle): mixed) $operation
     */
    #[DataProvider('provideOperations')]
    public function testCloseThrowsOnSubsequentOperations(Closure $operation): void
    {
        $handle = new IO\ConcatReadHandle(new IO\IterableReadHandle(['hello']), new IO\IterableReadHandle(['world']));
        $handle->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        $operation($handle);
    }

    /**
     * @return iterable<string, array{(Closure(IO\ConcatReadHandle): mixed)}>
     */
    public static function provideOperations(): iterable
    {
        yield 'read' => [static fn(IO\ConcatReadHandle $h) => $h->read()];
        yield 'readAll' => [static fn(IO\ConcatReadHandle $h) => $h->readAll()];
        yield 'readFixedSize' => [static fn(IO\ConcatReadHandle $h) => $h->readFixedSize(1)];
        yield 'tryRead' => [static fn(IO\ConcatReadHandle $h) => $h->tryRead()];
        yield 'reachedEndOfDataSource' => [static fn(IO\ConcatReadHandle $h) => $h->reachedEndOfDataSource()];
    }

    public function testReadAcrossBothHandles(): void
    {
        $handle = new IO\ConcatReadHandle(
            new IO\IterableReadHandle(['hello', ' ']),
            new IO\IterableReadHandle(['world']),
        );

        static::assertSame('hello', $handle->read());
        static::assertSame(' ', $handle->read());
        static::assertSame('world', $handle->read());
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadAllAcrossBothHandles(): void
    {
        $handle = new IO\ConcatReadHandle(
            new IO\IterableReadHandle(['hello', ' ']),
            new IO\IterableReadHandle(['world']),
        );

        static::assertSame('hello world', $handle->readAll());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testTryReadReturnsBufferedDataFromFirstThenSecond(): void
    {
        $first = new IO\IterableReadHandle(['hello']);
        $second = new IO\IterableReadHandle(['world']);
        $handle = new IO\ConcatReadHandle($first, $second);

        static::assertSame('', $handle->tryRead());

        static::assertSame('hello', $handle->read());
        static::assertSame('', $handle->tryRead());

        static::assertSame('world', $handle->read());
        static::assertSame('', $handle->tryRead());
    }

    public function testTryReadWithBufferedDataInFirst(): void
    {
        $first = new IO\IterableReadHandle(['hello world']);
        $second = new IO\IterableReadHandle(['!']);
        $handle = new IO\ConcatReadHandle($first, $second);

        $handle->read(5);

        static::assertSame(' world', $handle->tryRead());
    }

    public function testTryReadSwitchesToSecondAfterFirstEof(): void
    {
        $first = new IO\IterableReadHandle(['ab']);
        $second = new IO\IterableReadHandle(['cd ef']);
        $handle = new IO\ConcatReadHandle($first, $second);

        static::assertSame('ab', $handle->read());

        static::assertSame('cd', $handle->read(2));

        static::assertSame(' ef', $handle->tryRead());
    }

    public function testReachedEndOfDataSourceOnlyWhenBothExhausted(): void
    {
        $handle = new IO\ConcatReadHandle(new IO\IterableReadHandle(['a']), new IO\IterableReadHandle(['b']));

        static::assertFalse($handle->reachedEndOfDataSource());

        $handle->read();
        static::assertFalse($handle->reachedEndOfDataSource());

        $handle->read();
        static::assertFalse($handle->reachedEndOfDataSource());

        $handle->read();
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testCloseClosesBothHandles(): void
    {
        $first = new IO\IterableReadHandle(['hello']);
        $second = new IO\IterableReadHandle(['world']);
        $handle = new IO\ConcatReadHandle($first, $second);

        $handle->close();

        static::assertTrue($handle->isClosed());
        static::assertTrue($first->isClosed());
        static::assertTrue($second->isClosed());
    }

    public function testCloseWithNonCloseableHandles(): void
    {
        $nonCloseable = static fn(): IO\ReadHandleInterface => new class() implements IO\ReadHandleInterface {
            use IO\ReadHandleConvenienceMethodsTrait;

            #[Override]
            public function reachedEndOfDataSource(): bool
            {
                return true;
            }

            #[Override]
            public function tryRead(null|int $maxBytes = null): string
            {
                return '';
            }

            #[Override]
            public function read(
                null|int $maxBytes = null,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): string {
                return '';
            }
        };

        $handle = new IO\ConcatReadHandle($nonCloseable(), $nonCloseable());

        $handle->close();

        static::assertTrue($handle->isClosed());
    }

    public function testIsClosedReturnsFalseByDefault(): void
    {
        $handle = new IO\ConcatReadHandle(new IO\IterableReadHandle(['a']), new IO\IterableReadHandle(['b']));

        static::assertFalse($handle->isClosed());
    }

    public function testWithEmptyFirstHandle(): void
    {
        $handle = new IO\ConcatReadHandle(new IO\IterableReadHandle([]), new IO\IterableReadHandle(['world']));

        static::assertSame('world', $handle->read());
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testWithEmptySecondHandle(): void
    {
        $handle = new IO\ConcatReadHandle(new IO\IterableReadHandle(['hello']), new IO\IterableReadHandle([]));

        static::assertSame('hello', $handle->read());
        static::assertSame('', $handle->read());
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testWithBothHandlesEmpty(): void
    {
        $handle = new IO\ConcatReadHandle(new IO\IterableReadHandle([]), new IO\IterableReadHandle([]));

        static::assertSame('', $handle->read());
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadWithMaxBytesSpanningBoundary(): void
    {
        $handle = new IO\ConcatReadHandle(new IO\IterableReadHandle(['ab']), new IO\IterableReadHandle(['cd']));

        static::assertSame('a', $handle->read(1));
        static::assertSame('b', $handle->read(1));
        static::assertSame('c', $handle->read(1));
        static::assertSame('d', $handle->read(1));
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadFixedSizeSpanningBoundary(): void
    {
        $handle = new IO\ConcatReadHandle(
            new IO\IterableReadHandle(['he', 'llo']),
            new IO\IterableReadHandle([' world']),
        );

        static::assertSame('hello world', $handle->readFixedSize(11));
    }

    public function testReadWithMaxBytesLargerThanFirstHandle(): void
    {
        $handle = new IO\ConcatReadHandle(new IO\IterableReadHandle(['ab']), new IO\IterableReadHandle(['cdef']));

        static::assertSame('ab', $handle->read(10));
        static::assertSame('cdef', $handle->read(10));
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }
}
