<?php

declare(strict_types=1);

namespace Psl\IO\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\IO;

final class SinkReadHandleTest extends TestCase
{
    public function testReachedEndOfDataSourceImmediately(): void
    {
        $handle = new IO\SinkReadHandle();

        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadReturnsEmpty(): void
    {
        $handle = new IO\SinkReadHandle();

        static::assertSame('', $handle->read());
        static::assertSame('', $handle->read(100));
    }

    public function testTryReadReturnsEmpty(): void
    {
        $handle = new IO\SinkReadHandle();

        static::assertSame('', $handle->tryRead());
        static::assertSame('', $handle->tryRead(100));
    }

    public function testReadAllReturnsEmpty(): void
    {
        $handle = new IO\SinkReadHandle();

        static::assertSame('', $handle->readAll());
    }

    public function testIsClosedReturnsFalseByDefault(): void
    {
        $handle = new IO\SinkReadHandle();

        static::assertFalse($handle->isClosed());
    }

    public function testClose(): void
    {
        $handle = new IO\SinkReadHandle();

        $handle->close();

        static::assertTrue($handle->isClosed());
    }

    /**
     * @param (Closure(IO\SinkReadHandle): mixed) $operation
     */
    #[DataProvider('provideOperations')]
    public function testAllOperationsThrowAfterClose(Closure $operation): void
    {
        $handle = new IO\SinkReadHandle();
        $handle->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        $operation($handle);
    }

    /**
     * @return iterable<string, array{(Closure(IO\SinkReadHandle): mixed)}>
     */
    public static function provideOperations(): iterable
    {
        yield 'read' => [static fn(IO\SinkReadHandle $h) => $h->read()];
        yield 'readAll' => [static fn(IO\SinkReadHandle $h) => $h->readAll()];
        yield 'tryRead' => [static fn(IO\SinkReadHandle $h) => $h->tryRead()];
        yield 'reachedEndOfDataSource' => [static fn(IO\SinkReadHandle $h) => $h->reachedEndOfDataSource()];
    }

    public function testDiffersFromEmptyMemoryHandle(): void
    {
        $memory = new IO\MemoryHandle('');
        static::assertFalse($memory->reachedEndOfDataSource());

        $sink = new IO\SinkReadHandle();
        static::assertTrue($sink->reachedEndOfDataSource());
    }
}
