<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal;

use Closure;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

/**
 * Decorating body handle that releases an HTTP/1.x connection back to the pool
 * when the response body is fully consumed or the handle is closed.
 *
 * This prevents premature connection reuse: the underlying TCP/TLS stream stays
 * checked out of the pool until the consumer has read the entire response body.
 * The release callback is invoked exactly once, either when {@see read()},
 * {@see tryRead()}, or {@see reachedEndOfDataSource()} detects that the inner
 * handle has reached EOF, or when {@see close()} is called explicitly (or via GC).
 *
 * @internal
 *
 * @see H1\H1Connection Creates this wrapper when keep-alive and pool release are both active.
 */
final class PoolReleasingBodyHandle implements IO\ReadHandleInterface, IO\CloseHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private bool $closed = false;

    private bool $fullyConsumed = false;

    /**
     * @param IO\ReadHandleInterface $inner The inner body handle to read from.
     * @param Closure(bool): void $onComplete Called exactly once with true if the body was fully consumed (safe to reuse), or false if the body was discarded (connection tainted).
     */
    public function __construct(
        private readonly IO\ReadHandleInterface $inner,
        private readonly Closure $onComplete,
    ) {}

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Return available data from the inner handle without blocking.
     *
     * Checks for EOF after reading and releases the connection if done.
     *
     * @inheritDoc
     */
    public function tryRead(null|int $maxBytes = null): string
    {
        if ($this->closed) {
            return '';
        }

        $data = $this->inner->tryRead($maxBytes);
        $this->closeIfDone();

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
        if ($this->closed) {
            return '';
        }

        $data = $this->inner->read($maxBytes, $cancellation);
        $this->closeIfDone();

        return $data;
    }

    /**
     * Whether the inner body has been fully consumed.
     *
     * Triggers the pool release callback as a side effect if EOF is detected.
     */
    public function reachedEndOfDataSource(): bool
    {
        if ($this->closed) {
            return true;
        }

        $eof = $this->inner->reachedEndOfDataSource();
        if ($eof) {
            $this->fullyConsumed = true;
            $this->close();
        }

        return $eof;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function close(): void
    {
        if (!$this->closed) {
            $this->closed = true;

            if ($this->inner instanceof IO\CloseHandleInterface) {
                $this->inner->close();
            }

            ($this->onComplete)($this->fullyConsumed);
        }
    }

    /**
     * Check if the inner handle has reached EOF and close if so.
     */
    private function closeIfDone(): void
    {
        if ($this->inner->reachedEndOfDataSource()) {
            $this->fullyConsumed = true;
            $this->close();
        }
    }
}
