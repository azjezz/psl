<?php

declare(strict_types=1);

namespace Psl\IO\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\IO;

final class JoinedReadWriteHandleTest extends TestCase
{
    public function testReadDelegatesToReader(): void
    {
        $reader = new IO\MemoryHandle('hello world');
        $writer = new IO\MemoryHandle();
        $handle = new IO\JoinedReadWriteHandle($reader, $writer);

        static::assertSame('hello', $handle->read(5));
        static::assertSame(' world', $handle->read());
    }

    public function testTryReadDelegatesToReader(): void
    {
        $reader = new IO\MemoryHandle('hello');
        $writer = new IO\MemoryHandle();
        $handle = new IO\JoinedReadWriteHandle($reader, $writer);

        static::assertSame('hello', $handle->tryRead());
    }

    public function testReachedEndOfDataSourceDelegatesToReader(): void
    {
        $reader = new IO\MemoryHandle('data');
        $writer = new IO\MemoryHandle();
        $handle = new IO\JoinedReadWriteHandle($reader, $writer);

        static::assertFalse($handle->reachedEndOfDataSource());

        $handle->readAll();

        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testWriteDelegatesToWriter(): void
    {
        $reader = new IO\MemoryHandle();
        $writer = new IO\MemoryHandle();
        $handle = new IO\JoinedReadWriteHandle($reader, $writer);

        $written = $handle->write('hello');

        static::assertSame(5, $written);
        static::assertSame('hello', $writer->getBuffer());
    }

    public function testTryWriteDelegatesToWriter(): void
    {
        $reader = new IO\MemoryHandle();
        $writer = new IO\MemoryHandle();
        $handle = new IO\JoinedReadWriteHandle($reader, $writer);

        $written = $handle->tryWrite('world');

        static::assertSame(5, $written);
        static::assertSame('world', $writer->getBuffer());
    }

    public function testWriteAllDelegatesToWriter(): void
    {
        $reader = new IO\MemoryHandle();
        $writer = new IO\MemoryHandle();
        $handle = new IO\JoinedReadWriteHandle($reader, $writer);

        $handle->writeAll('hello world');

        static::assertSame('hello world', $writer->getBuffer());
    }

    public function testReadAndWriteAreIndependent(): void
    {
        $reader = new IO\MemoryHandle('input data');
        $writer = new IO\MemoryHandle();
        $handle = new IO\JoinedReadWriteHandle($reader, $writer);

        static::assertSame('input', $handle->read(5));
        $handle->write('output');
        static::assertSame(' data', $handle->read());

        static::assertSame('output', $writer->getBuffer());
    }

    public function testCloseClosesBothHandles(): void
    {
        $reader = new IO\MemoryHandle('data');
        $writer = new IO\MemoryHandle();
        $handle = new IO\JoinedReadWriteHandle($reader, $writer);

        $handle->close();

        static::assertTrue($handle->isClosed());
        static::assertTrue($reader->isClosed());
        static::assertTrue($writer->isClosed());
    }

    public function testCloseWithNonClosableHandles(): void
    {
        $reader = new IO\IterableReadHandle(['data']);
        $writer = new IO\MemoryHandle();
        $handle = new IO\JoinedReadWriteHandle($reader, $writer);

        $handle->close();

        static::assertTrue($handle->isClosed());
        static::assertTrue($reader->isClosed());
        static::assertTrue($writer->isClosed());
    }

    public function testIsClosedReturnsFalseByDefault(): void
    {
        $handle = new IO\JoinedReadWriteHandle(new IO\MemoryHandle(), new IO\MemoryHandle());

        static::assertFalse($handle->isClosed());
    }

    /**
     * @param (Closure(IO\JoinedReadWriteHandle): mixed) $operation
     */
    #[DataProvider('provideOperations')]
    public function testThrowsAfterClose(Closure $operation): void
    {
        $handle = new IO\JoinedReadWriteHandle(new IO\MemoryHandle('data'), new IO\MemoryHandle());
        $handle->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        $operation($handle);
    }

    /**
     * @return iterable<string, array{(Closure(IO\JoinedReadWriteHandle): mixed)}>
     */
    public static function provideOperations(): iterable
    {
        yield 'read' => [static fn(IO\JoinedReadWriteHandle $h) => $h->read()];
        yield 'readAll' => [static fn(IO\JoinedReadWriteHandle $h) => $h->readAll()];
        yield 'readFixedSize' => [static fn(IO\JoinedReadWriteHandle $h) => $h->readFixedSize(1)];
        yield 'tryRead' => [static fn(IO\JoinedReadWriteHandle $h) => $h->tryRead()];
        yield 'reachedEndOfDataSource' => [static fn(IO\JoinedReadWriteHandle $h) => $h->reachedEndOfDataSource()];
        yield 'write' => [static fn(IO\JoinedReadWriteHandle $h) => $h->write('data')];
        yield 'writeAll' => [static fn(IO\JoinedReadWriteHandle $h) => $h->writeAll('data')];
        yield 'tryWrite' => [static fn(IO\JoinedReadWriteHandle $h) => $h->tryWrite('data')];
    }
}
