<?php

declare(strict_types=1);

namespace Psl\IO\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

use function str_repeat;

final class SinkReadWriteHandleTest extends TestCase
{
    public function testTryReadReturnsEmptyString(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        static::assertSame('', $handle->tryRead());
    }

    public function testTryReadWithMaxBytesReturnsEmptyString(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        static::assertSame('', $handle->tryRead(1024));
    }

    public function testReadReturnsEmptyString(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        static::assertSame('', $handle->read());
    }

    public function testReadWithMaxBytesReturnsEmptyString(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        static::assertSame('', $handle->read(1024));
    }

    public function testReadWithCancellationToken(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $data = $handle->read(cancellation: new Async\TimeoutCancellationToken(Duration::seconds(5)));

        static::assertSame('', $data);
    }

    public function testReadAllReturnsEmptyString(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        static::assertSame('', $handle->readAll());
    }

    public function testReadAllWithMaxBytesReturnsEmptyString(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        static::assertSame('', $handle->readAll(1024));
    }

    public function testReadAllWithCancellationToken(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $data = $handle->readAll(cancellation: new Async\TimeoutCancellationToken(Duration::seconds(5)));

        static::assertSame('', $data);
    }

    public function testReadFixedSizeThrowsRuntimeException(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $this->expectException(IO\Exception\RuntimeException::class);
        $this->expectExceptionMessage('10 bytes were requested, but only able to read 0 bytes');

        $handle->readFixedSize(10);
    }

    public function testReachedEndOfDataSourceReturnsTrue(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReachedEndOfDataSourceAlwaysReturnsTrue(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $handle->tryRead();

        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testTryWriteDiscardsData(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $written = $handle->tryWrite('hello, world!');

        static::assertSame(13, $written);
    }

    public function testTryWriteEmptyString(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $written = $handle->tryWrite('');

        static::assertSame(0, $written);
    }

    public function testWriteDiscardsData(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $written = $handle->write('hello, world!');

        static::assertSame(13, $written);
    }

    public function testWriteEmptyString(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $written = $handle->write('');

        static::assertSame(0, $written);
    }

    public function testWriteAllDiscardsData(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $handle->writeAll('hello, world!');

        static::assertFalse($handle->isClosed());
    }

    public function testWriteAllEmptyString(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $handle->writeAll('');

        static::assertFalse($handle->isClosed());
    }

    public function testWriteWithCancellationToken(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $written = $handle->write('data', new Async\TimeoutCancellationToken(Duration::seconds(5)));

        static::assertSame(4, $written);
    }

    public function testWriteAllWithCancellationToken(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $handle->writeAll('data', new Async\TimeoutCancellationToken(Duration::seconds(5)));

        static::assertFalse($handle->isClosed());
    }

    public function testWriteLargeData(): void
    {
        $handle = new IO\SinkReadWriteHandle();
        $data = str_repeat('x', 1024 * 1024);

        $written = $handle->tryWrite($data);

        static::assertSame(1024 * 1024, $written);
    }

    public function testMultipleWrites(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        static::assertSame(5, $handle->tryWrite('hello'));
        static::assertSame(1, $handle->tryWrite(','));
        static::assertSame(6, $handle->tryWrite(' world'));
    }

    public function testWriteDoesNotAffectRead(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $handle->writeAll('hello, world!');

        static::assertSame('', $handle->read());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testIsClosedReturnsFalseByDefault(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        static::assertFalse($handle->isClosed());
    }

    public function testIsClosedReturnsTrueAfterClose(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $handle->close();

        static::assertTrue($handle->isClosed());
    }

    public function testCloseIsIdempotent(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $handle->close();
        $handle->close();

        static::assertTrue($handle->isClosed());
    }

    /**
     * @param (Closure(IO\SinkReadWriteHandle): mixed) $operation
     */
    #[DataProvider('provideOperationsAfterClose')]
    public function testOperationsThrowAfterClose(Closure $operation): void
    {
        $handle = new IO\SinkReadWriteHandle();
        $handle->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        $operation($handle);
    }

    /**
     * @return iterable<string, array{(Closure(IO\SinkReadWriteHandle): mixed)}>
     */
    public static function provideOperationsAfterClose(): iterable
    {
        yield 'tryRead' => [
            static fn(IO\SinkReadWriteHandle $handle): string => $handle->tryRead(),
        ];

        yield 'read' => [
            static fn(IO\SinkReadWriteHandle $handle): string => $handle->read(),
        ];

        yield 'readAll' => [
            static fn(IO\SinkReadWriteHandle $handle): string => $handle->readAll(),
        ];

        yield 'reachedEndOfDataSource' => [
            static fn(IO\SinkReadWriteHandle $handle): bool => $handle->reachedEndOfDataSource(),
        ];

        yield 'tryWrite' => [
            static fn(IO\SinkReadWriteHandle $handle): int => $handle->tryWrite('data'),
        ];

        yield 'write' => [
            static fn(IO\SinkReadWriteHandle $handle): int => $handle->write('data'),
        ];

        yield 'writeAll' => [
            static fn(IO\SinkReadWriteHandle $handle): null => $handle->writeAll('data'),
        ];

        yield 'flush' => [
            static fn(IO\SinkReadWriteHandle $handle): null => $handle->flush(),
        ];
    }

    public function testFlushIsNoOp(): void
    {
        $handle = new IO\SinkReadWriteHandle();

        $handle->writeAll('data');
        $handle->flush();

        static::assertFalse($handle->isClosed());
    }
}
