<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Client\Internal\LimitedReadHandle;
use Psl\IO;
use Psl\IO\MemoryHandle;

use function min;
use function str_repeat;

final class LimitedReadHandleTest extends TestCase
{
    public function testTryReadReturnsDataWithinLimit(): void
    {
        $inner = new MemoryHandle('hello');
        $handle = new LimitedReadHandle($inner, 10);

        $data = $handle->tryRead();

        static::assertSame('hello', $data);
    }

    public function testTryReadReturnsEmptyWhenInnerEmpty(): void
    {
        $inner = new MemoryHandle('');
        $handle = new LimitedReadHandle($inner, 10);

        $data = $handle->tryRead();

        static::assertSame('', $data);
    }

    public function testHandleLimitReachedReturnsEmptyWhenInnerReachedEof(): void
    {
        $inner = new class() implements IO\ReadHandleInterface {
            use IO\ReadHandleConvenienceMethodsTrait;

            private int $remaining = 3;

            public function tryRead(null|int $maxBytes = null): string
            {
                if ($this->remaining <= 0) {
                    return '';
                }

                $bytes = $maxBytes !== null ? min($maxBytes, $this->remaining) : $this->remaining;
                $this->remaining -= $bytes;

                return str_repeat('x', $bytes);
            }

            public function read(
                null|int $maxBytes = null,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): string {
                return $this->tryRead($maxBytes);
            }

            public function reachedEndOfDataSource(): bool
            {
                return $this->remaining <= 0;
            }
        };

        $handle = new LimitedReadHandle($inner, 3);

        $data = $handle->tryRead();
        static::assertSame('xxx', $data);

        $more = $handle->tryRead();
        static::assertSame('', $more);
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testHandleLimitReachedReturnsEmptyWhenPeekEmpty(): void
    {
        $inner = new MemoryHandle('12345');
        $handle = new LimitedReadHandle($inner, 5);

        $data = $handle->read(5);
        static::assertSame('12345', $data);

        $more = $handle->tryRead();
        static::assertSame('', $more);
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testHandleLimitReachedThrowsWhenMoreDataAvailable(): void
    {
        $inner = new MemoryHandle('123456');
        $handle = new LimitedReadHandle($inner, 5);

        $data = $handle->tryRead();
        static::assertSame('12345', $data);

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Response body exceeds maximum allowed size of 5 bytes.');

        $handle->tryRead();
    }
}
