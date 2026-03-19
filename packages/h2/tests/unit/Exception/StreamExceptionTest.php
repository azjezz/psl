<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Psl\H2\Exception\StreamException;

final class StreamExceptionTest extends TestCase
{
    public function testForInvalidState(): void
    {
        $exception = StreamException::forInvalidState(1, 'open', 'closed');

        static::assertSame('Stream 1 is in state closed, expected open.', $exception->getMessage());
    }

    public function testForInvalidStateWithDifferentStreamId(): void
    {
        $exception = StreamException::forInvalidState(42, 'idle', 'half-closed');

        static::assertSame('Stream 42 is in state half-closed, expected idle.', $exception->getMessage());
    }

    public function testForStreamClosed(): void
    {
        $exception = StreamException::forStreamClosed(5);

        static::assertSame('Stream 5 is closed.', $exception->getMessage());
    }

    public function testForStreamClosedWithDifferentStreamId(): void
    {
        $exception = StreamException::forStreamClosed(99);

        static::assertSame('Stream 99 is closed.', $exception->getMessage());
    }

    public function testForStreamRefused(): void
    {
        $exception = StreamException::forStreamRefused(3);

        static::assertSame('Stream 3 was refused.', $exception->getMessage());
    }

    public function testForStreamRefusedWithDifferentStreamId(): void
    {
        $exception = StreamException::forStreamRefused(17);

        static::assertSame('Stream 17 was refused.', $exception->getMessage());
    }

    public function testForStreamReset(): void
    {
        $exception = StreamException::forStreamReset(7, 2);

        static::assertSame('Stream 7 was reset with error code 2.', $exception->getMessage());
    }

    public function testForStreamResetWithDifferentValues(): void
    {
        $exception = StreamException::forStreamReset(11, 8);

        static::assertSame('Stream 11 was reset with error code 8.', $exception->getMessage());
    }
}
