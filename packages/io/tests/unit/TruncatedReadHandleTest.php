<?php

declare(strict_types=1);

namespace Psl\IO\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

final class TruncatedReadHandleTest extends TestCase
{
    public function testReadingStopsAtLimit(): void
    {
        $inner = new IO\IterableReadHandle(['hello world']);
        $handle = new IO\TruncatedReadHandle($inner, 5);

        static::assertSame('hello', $handle->readAll());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testTryReadRespectsLimit(): void
    {
        $inner = new IO\IterableReadHandle(['hello world']);
        $inner->read(5);

        $handle = new IO\TruncatedReadHandle($inner, 3);

        $data = $handle->tryRead();
        static::assertSame(' wo', $data);

        $data = $handle->tryRead();
        static::assertSame('', $data);
    }

    public function testReachedEndOfDataSourceAtLimit(): void
    {
        $inner = new IO\IterableReadHandle(['hello world']);
        $handle = new IO\TruncatedReadHandle($inner, 5);

        static::assertFalse($handle->reachedEndOfDataSource());

        $handle->read(5);

        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReachedEndOfDataSourceWhenUnderlyingEofBeforeLimit(): void
    {
        $inner = new IO\IterableReadHandle(['hi']);
        $handle = new IO\TruncatedReadHandle($inner, 100);

        $handle->readAll();

        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testMaxBytesIsCappedToRemainingAllowance(): void
    {
        $inner = new IO\IterableReadHandle(['abcdefghij']);
        $handle = new IO\TruncatedReadHandle($inner, 5);

        $data = $handle->read(10);
        static::assertSame('abcde', $data);

        $data = $handle->read(10);
        static::assertSame('', $data);
    }

    public function testCloseBehavior(): void
    {
        $inner = new IO\IterableReadHandle(['hello']);
        $handle = new IO\TruncatedReadHandle($inner, 5);

        static::assertFalse($handle->isClosed());

        $handle->close();

        static::assertTrue($handle->isClosed());
        static::assertTrue($inner->isClosed());
    }

    public function testCloseDoesNotCloseNonCloseableHandle(): void
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
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): string {
                return '';
            }
        };

        $handle = new IO\TruncatedReadHandle($inner, 5);
        $handle->close();

        static::assertTrue($handle->isClosed());
    }

    /**
     * @param (Closure(IO\TruncatedReadHandle): mixed) $operation
     */
    #[DataProvider('provideOperations')]
    public function testAllOperationsThrowAfterClose(Closure $operation): void
    {
        $inner = new IO\IterableReadHandle(['hello']);
        $handle = new IO\TruncatedReadHandle($inner, 5);
        $handle->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        $operation($handle);
    }

    /**
     * @return iterable<string, array{(Closure(IO\TruncatedReadHandle): mixed)}>
     */
    public static function provideOperations(): iterable
    {
        yield 'read' => [static fn(IO\TruncatedReadHandle $h) => $h->read()];
        yield 'readAll' => [static fn(IO\TruncatedReadHandle $h) => $h->readAll()];
        yield 'readFixedSize' => [static fn(IO\TruncatedReadHandle $h) => $h->readFixedSize(1)];
        yield 'tryRead' => [static fn(IO\TruncatedReadHandle $h) => $h->tryRead()];
        yield 'reachedEndOfDataSource' => [static fn(IO\TruncatedReadHandle $h) => $h->reachedEndOfDataSource()];
    }

    public function testLimitOfZeroImmediatelyReportsEof(): void
    {
        $inner = new IO\IterableReadHandle(['hello']);
        $handle = new IO\TruncatedReadHandle($inner, 0);

        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadingExactlyTheLimit(): void
    {
        $inner = new IO\IterableReadHandle(['hello']);
        $handle = new IO\TruncatedReadHandle($inner, 5);

        $data = $handle->read(5);
        static::assertSame('hello', $data);

        static::assertTrue($handle->reachedEndOfDataSource());
        static::assertSame('', $handle->read());
    }

    public function testUnderlyingHandleHasMoreDataButWeStopAtLimit(): void
    {
        $inner = new IO\IterableReadHandle(['hello world, this is a long string']);
        $handle = new IO\TruncatedReadHandle($inner, 5);

        static::assertSame('hello', $handle->readAll());
        static::assertTrue($handle->reachedEndOfDataSource());

        static::assertFalse($inner->reachedEndOfDataSource());
        static::assertSame(' world, this is a long string', $inner->tryRead());
    }
}
