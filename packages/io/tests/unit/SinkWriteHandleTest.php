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

final class SinkWriteHandleTest extends TestCase
{
    public function testTryWriteDiscardsData(): void
    {
        $handle = new IO\SinkWriteHandle();

        $written = $handle->tryWrite('hello, world!');

        static::assertSame(13, $written);
    }

    public function testTryWriteEmptyString(): void
    {
        $handle = new IO\SinkWriteHandle();

        $written = $handle->tryWrite('');

        static::assertSame(0, $written);
    }

    public function testWriteDiscardsData(): void
    {
        $handle = new IO\SinkWriteHandle();

        $written = $handle->write('hello, world!');

        static::assertSame(13, $written);
    }

    public function testWriteEmptyString(): void
    {
        $handle = new IO\SinkWriteHandle();

        $written = $handle->write('');

        static::assertSame(0, $written);
    }

    public function testWriteAllDiscardsData(): void
    {
        $handle = new IO\SinkWriteHandle();

        $handle->writeAll('hello, world!');

        static::assertFalse($handle->isClosed());
    }

    public function testWriteAllEmptyString(): void
    {
        $handle = new IO\SinkWriteHandle();

        $handle->writeAll('');

        static::assertFalse($handle->isClosed());
    }

    public function testWriteWithCancellationToken(): void
    {
        $handle = new IO\SinkWriteHandle();

        $written = $handle->write('data', new Async\TimeoutCancellationToken(Duration::seconds(5)));

        static::assertSame(4, $written);
    }

    public function testWriteAllWithCancellationToken(): void
    {
        $handle = new IO\SinkWriteHandle();

        $handle->writeAll('data', new Async\TimeoutCancellationToken(Duration::seconds(5)));

        static::assertFalse($handle->isClosed());
    }

    public function testWriteLargeData(): void
    {
        $handle = new IO\SinkWriteHandle();
        $data = str_repeat('x', 1024 * 1024);

        $written = $handle->tryWrite($data);

        static::assertSame(1024 * 1024, $written);
    }

    public function testMultipleWrites(): void
    {
        $handle = new IO\SinkWriteHandle();

        static::assertSame(5, $handle->tryWrite('hello'));
        static::assertSame(1, $handle->tryWrite(','));
        static::assertSame(6, $handle->tryWrite(' world'));
    }

    public function testIsClosedReturnsFalseByDefault(): void
    {
        $handle = new IO\SinkWriteHandle();

        static::assertFalse($handle->isClosed());
    }

    public function testIsClosedReturnsTrueAfterClose(): void
    {
        $handle = new IO\SinkWriteHandle();

        $handle->close();

        static::assertTrue($handle->isClosed());
    }

    public function testCloseIsIdempotent(): void
    {
        $handle = new IO\SinkWriteHandle();

        $handle->close();
        $handle->close();

        static::assertTrue($handle->isClosed());
    }

    /**
     * @param (Closure(IO\SinkWriteHandle): mixed) $operation
     */
    #[DataProvider('provideOperationsAfterClose')]
    public function testOperationsThrowAfterClose(Closure $operation): void
    {
        $handle = new IO\SinkWriteHandle();
        $handle->close();

        $this->expectException(IO\Exception\AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        $operation($handle);
    }

    /**
     * @return iterable<string, array{(Closure(IO\SinkWriteHandle): mixed)}>
     */
    public static function provideOperationsAfterClose(): iterable
    {
        yield 'tryWrite' => [
            static fn(IO\SinkWriteHandle $handle): int => $handle->tryWrite('data'),
        ];

        yield 'write' => [
            static fn(IO\SinkWriteHandle $handle): int => $handle->write('data'),
        ];

        yield 'writeAll' => [
            static fn(IO\SinkWriteHandle $handle): null => $handle->writeAll('data'),
        ];
    }
}
