<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal\H1;

use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\Internal\H1\FixedLengthBodyHandle;
use Psl\HTTP\Client\Tests\Fixture\H1\FakeStream;
use Psl\IO;

use function str_repeat;
use function strlen;

final class FixedLengthBodyHandleTest extends TestCase
{
    private static function handle(string $raw, int $length): FixedLengthBodyHandle
    {
        /** @var non-negative-int $length */
        return new FixedLengthBodyHandle(new IO\Reader(new FakeStream($raw)), $length);
    }

    public function testExactLength(): void
    {
        $handle = self::handle('hello', 5);

        static::assertSame('hello', $handle->readAll());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadInChunks(): void
    {
        $handle = self::handle('abcdef', 6);

        static::assertSame('abc', $handle->read(3));
        static::assertFalse($handle->reachedEndOfDataSource());
        static::assertSame('def', $handle->read(3));
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testZeroLength(): void
    {
        $handle = self::handle('ignored', 0);

        static::assertSame('', $handle->readAll());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testPrematureEof(): void
    {
        $handle = self::handle('short', 100);

        $data = $handle->readAll();
        static::assertSame('short', $data);
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testTryReadReturnsAvailableData(): void
    {
        $handle = self::handle('hello', 5);

        $data = $handle->tryRead(3);
        static::assertSame('hel', $data);
    }

    public function testReadAllWithLargeBody(): void
    {
        $body = str_repeat('X', 10_000);
        $handle = self::handle($body, 10_000);

        static::assertSame(10_000, strlen($handle->readAll()));
    }

    public function testTryReadReturnsEmptyWhenNoData(): void
    {
        $stream = new class() implements IO\ReadHandleInterface {
            use IO\ReadHandleConvenienceMethodsTrait;

            public function tryRead(null|int $maxBytes = null): string
            {
                return '';
            }

            public function read(
                null|int $maxBytes = null,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): string {
                return 'data';
            }

            public function reachedEndOfDataSource(): bool
            {
                return false;
            }
        };

        $handle = new FixedLengthBodyHandle(new IO\Reader($stream), 10);

        static::assertSame('', $handle->tryRead());
        static::assertFalse($handle->reachedEndOfDataSource());
    }

    public function testTryReadReturnsPartialData(): void
    {
        $handle = self::handle('hello world', 100);

        $data = $handle->tryRead(5);
        static::assertSame('hello', $data);
        static::assertFalse($handle->reachedEndOfDataSource());
    }

    public function testTryReadExactContentLength(): void
    {
        $handle = self::handle('hello', 5);

        $data = $handle->tryRead(5);
        static::assertSame('hello', $data);
        static::assertTrue($handle->reachedEndOfDataSource());

        static::assertSame('', $handle->tryRead());
    }

    public function testTryReadInMultipleChunks(): void
    {
        $handle = self::handle('abcdefghij', 10);

        static::assertSame('abc', $handle->tryRead(3));
        static::assertFalse($handle->reachedEndOfDataSource());

        static::assertSame('def', $handle->tryRead(3));
        static::assertFalse($handle->reachedEndOfDataSource());

        static::assertSame('ghij', $handle->tryRead(4));
        static::assertTrue($handle->reachedEndOfDataSource());

        static::assertSame('', $handle->tryRead());
    }

    public function testTryReadReachedEndOfDataSourceFalseWhileReading(): void
    {
        $handle = self::handle('abcdefghij', 10);

        $data = $handle->tryRead(5);
        static::assertSame('abcde', $data);
        static::assertFalse($handle->reachedEndOfDataSource());
    }

    public function testTryReadPrematureEof(): void
    {
        $handle = self::handle('abc', 10);

        static::assertSame('abc', $handle->tryRead(10));
        static::assertFalse($handle->reachedEndOfDataSource());

        static::assertSame('', $handle->tryRead());
        static::assertTrue($handle->reachedEndOfDataSource());
    }
}
