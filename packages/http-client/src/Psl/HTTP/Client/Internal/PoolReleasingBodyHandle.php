<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal;

use Closure;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

/**
 * Decorating body handle that releases an HTTP/1.x connection back to the pool
 * when the response body is fully consumed.
 *
 * This prevents premature connection reuse: the underlying TCP/TLS stream stays
 * checked out of the pool until the consumer has read the entire response body.
 * The release callback is invoked exactly once, either when {@see read()},
 * {@see tryRead()}, or {@see reachedEndOfDataSource()} detects that the inner
 * handle has reached EOF.
 *
 * @internal
 *
 * @see H1\H1Connection Creates this wrapper when keep-alive and pool release are both active.
 */
final class PoolReleasingBodyHandle implements IO\ReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private bool $released = false;

    /**
     * @param IO\ReadHandleInterface $inner The inner body handle to read from.
     * @param Closure(): void $onComplete Called exactly once when the body is fully consumed.
     */
    public function __construct(
        private readonly IO\ReadHandleInterface $inner,
        private readonly Closure $onComplete,
    ) {}

    /**
     * Return available data from the inner handle without blocking.
     *
     * Checks for EOF after reading and releases the connection if done.
     *
     * @inheritDoc
     */
    public function tryRead(null|int $maxBytes = null): string
    {
        $data = $this->inner->tryRead($maxBytes);
        $this->releaseIfDone();

        return $data;
    }

    /**
     * Read data from the inner handle, releasing the connection if EOF is reached.
     *
     * @inheritDoc
     */
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        $data = $this->inner->read($maxBytes, $cancellation);
        $this->releaseIfDone();

        return $data;
    }

    /**
     * Whether the inner body has been fully consumed.
     *
     * Triggers the pool release callback as a side effect if EOF is detected.
     */
    public function reachedEndOfDataSource(): bool
    {
        $eof = $this->inner->reachedEndOfDataSource();
        if ($eof) {
            $this->release();
        }

        return $eof;
    }

    /**
     * Check if the inner handle has reached EOF and release if so.
     */
    private function releaseIfDone(): void
    {
        if ($this->inner->reachedEndOfDataSource()) {
            $this->release();
        }
    }

    /**
     * Invoke the release callback exactly once.
     */
    private function release(): void
    {
        if (!$this->released) {
            $this->released = true;
            ($this->onComplete)();
        }
    }
}
