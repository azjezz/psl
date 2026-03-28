<?php

declare(strict_types=1);

namespace Psl\IO\Tests\Unit;

use Closure;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\IO;

final class IterableReadHandleTest extends TestCase
{
    /**
     * @param (Closure(IO\IterableReadHandle): mixed) $operation
     */
    #[DataProvider('provideOperations')]
    public function testCloseThrowsOnSubsequentOperations(Closure $operation): void
    {
        $handle = new IO\IterableReadHandle(['hello']);
        $handle->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        $operation($handle);
    }

    /**
     * @return iterable<string, array{(Closure(IO\IterableReadHandle): mixed)}>
     */
    public static function provideOperations(): iterable
    {
        yield 'read' => [static fn(IO\IterableReadHandle $h) => $h->read()];
        yield 'readAll' => [static fn(IO\IterableReadHandle $h) => $h->readAll()];
        yield 'readFixedSize' => [static fn(IO\IterableReadHandle $h) => $h->readFixedSize(1)];
        yield 'tryRead' => [static fn(IO\IterableReadHandle $h) => $h->tryRead()];
        yield 'reachedEndOfDataSource' => [static fn(IO\IterableReadHandle $h) => $h->reachedEndOfDataSource()];
    }

    public function testReadFromArray(): void
    {
        $handle = new IO\IterableReadHandle(['hello', ' ', 'world']);

        static::assertSame('hello', $handle->read());
        static::assertSame(' ', $handle->read());
        static::assertSame('world', $handle->read());
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadFromGenerator(): void
    {
        $generator = (static function (): Generator {
            yield 'chunk1';
            yield 'chunk2';
            yield 'chunk3';
        })();

        $handle = new IO\IterableReadHandle($generator);

        static::assertSame('chunk1', $handle->read());
        static::assertSame('chunk2', $handle->read());
        static::assertSame('chunk3', $handle->read());
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadSkipsEmptyStrings(): void
    {
        $handle = new IO\IterableReadHandle(['', '', 'data', '', 'more']);

        static::assertSame('data', $handle->read());
        static::assertSame('more', $handle->read());
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadWithMaxBytes(): void
    {
        $handle = new IO\IterableReadHandle(['hello world']);

        static::assertSame('hello', $handle->read(5));
        static::assertSame(' worl', $handle->read(5));
        static::assertSame('d', $handle->read(5));
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadWithMaxBytesAcrossChunks(): void
    {
        $handle = new IO\IterableReadHandle(['ab', 'cd', 'ef']);

        static::assertSame('a', $handle->read(1));
        static::assertSame('b', $handle->read(1));
        static::assertSame('c', $handle->read(1));
        static::assertSame('d', $handle->read(1));
        static::assertSame('e', $handle->read(1));
        static::assertSame('f', $handle->read(1));
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testTryReadReturnsBufferedDataOnly(): void
    {
        $handle = new IO\IterableReadHandle(['hello', 'world']);

        static::assertSame('', $handle->tryRead());

        static::assertSame('hello', $handle->read());

        static::assertSame('', $handle->tryRead());
    }

    public function testTryReadReturnsPartialBuffer(): void
    {
        $handle = new IO\IterableReadHandle(['hello world']);

        $handle->read(5);

        static::assertSame(' w', $handle->tryRead(2));
        static::assertSame('orld', $handle->tryRead());
        static::assertSame('', $handle->tryRead());
    }

    public function testReadAllConsumesEverything(): void
    {
        $handle = new IO\IterableReadHandle(['hello', ' ', 'world']);

        static::assertSame('hello world', $handle->readAll());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadAllWithMaxBytes(): void
    {
        $handle = new IO\IterableReadHandle(['hello', ' ', 'world']);

        static::assertSame('hello', $handle->readAll(5));
        static::assertFalse($handle->reachedEndOfDataSource());
    }

    public function testReadFixedSize(): void
    {
        $handle = new IO\IterableReadHandle(['hello world']);

        static::assertSame('hello', $handle->readFixedSize(5));
        static::assertSame(' world', $handle->readFixedSize(6));
    }

    public function testReadFixedSizeThrowsOnShortRead(): void
    {
        $handle = new IO\IterableReadHandle(['hi']);

        $this->expectException(IO\Exception\RuntimeException::class);

        $handle->readFixedSize(10);
    }

    public function testEmptyIterable(): void
    {
        $handle = new IO\IterableReadHandle([]);

        static::assertFalse($handle->reachedEndOfDataSource());
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testIsClosedReturnsFalseByDefault(): void
    {
        $handle = new IO\IterableReadHandle(['data']);

        static::assertFalse($handle->isClosed());
    }

    public function testCloseMarksClosed(): void
    {
        $handle = new IO\IterableReadHandle(['data']);

        $handle->close();

        static::assertTrue($handle->isClosed());
    }

    public function testCloseDiscardsBuffer(): void
    {
        $handle = new IO\IterableReadHandle(['hello world']);

        $handle->read(5);
        static::assertSame(' world', $handle->tryRead());

        $handle->close();

        static::assertTrue($handle->isClosed());
    }

    public function testIteratorAggregateSupport(): void
    {
        $aggregate = new class implements \IteratorAggregate {
            public function getIterator(): \ArrayIterator
            {
                return new \ArrayIterator(['foo', 'bar']);
            }
        };

        $handle = new IO\IterableReadHandle($aggregate);

        static::assertSame('foo', $handle->read());
        static::assertSame('bar', $handle->read());
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testIteratorSupport(): void
    {
        $iterator = new \ArrayIterator(['one', 'two']);

        $handle = new IO\IterableReadHandle($iterator);

        static::assertSame('one', $handle->read());
        static::assertSame('two', $handle->read());
        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testLazyEvaluation(): void
    {
        $advanced = 0;
        $generator = (static function () use (&$advanced): Generator {
            $advanced++;
            yield 'first';
            $advanced++;
            yield 'second';
            $advanced++;
            yield 'third';
        })();

        $handle = new IO\IterableReadHandle($generator);

        static::assertSame(0, $advanced);

        $handle->read();
        static::assertSame(1, $advanced);

        $handle->read();
        static::assertSame(2, $advanced);

        $handle->read();
        static::assertSame(3, $advanced);
    }

    public function testExceptionFromIteratorPropagates(): void
    {
        $generator = (static function (): Generator {
            yield 'ok';
            throw new \RuntimeException('iterator failed');
        })();

        $handle = new IO\IterableReadHandle($generator);

        static::assertSame('ok', $handle->read());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('iterator failed');

        $handle->read();
    }

    public function testOnlyEmptyStrings(): void
    {
        $handle = new IO\IterableReadHandle(['', '', '']);

        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }
}
