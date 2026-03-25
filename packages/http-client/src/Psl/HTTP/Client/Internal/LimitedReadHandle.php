<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Client\Internal\H2\ResponseBodyHandle;
use Psl\IO;
use Psl\IO\Exception;

use function min;
use function strlen;

/**
 * Decorating read handle that enforces a maximum response body size.
 *
 * Wraps an inner {@see IO\ReadHandleInterface} and tracks the total bytes read.
 * Once the limit is reached, one additional byte is peeked from the inner handle
 * to distinguish between "body fits exactly" (returns '' / EOF) and "body exceeds
 * limit" (throws {@see ProtocolException}).
 *
 * Read contract:
 * - {@see read()} and {@see tryRead()} return data from the inner handle up to the limit.
 * - If the inner handle has more data beyond the limit, a {@see ProtocolException} is thrown.
 * - If the inner handle reaches EOF at or before the limit, the handle reports EOF normally.
 *
 * @internal
 *
 * @see ResponseReader::buildBody() Wraps body handles when a max size is configured.
 * @see ResponseBodyHandle Uses its own size enforcement for H2.
 */
final class LimitedReadHandle implements IO\ReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    /** @var non-negative-int */
    private int $bytesRead = 0;

    private bool $limitReached = false;

    /**
     * @param IO\ReadHandleInterface $inner The inner body handle to read from.
     * @param positive-int $limit Maximum number of bytes allowed.
     */
    public function __construct(
        private readonly IO\ReadHandleInterface $inner,
        private readonly int $limit,
    ) {}

    /**
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws ProtocolException If the inner handle has more data beyond the limit.
     */
    public function tryRead(null|int $maxBytes = null): string
    {
        if ($this->bytesRead >= $this->limit) {
            return $this->handleLimitReached();
        }

        $remaining = $this->limit - $this->bytesRead;
        $effectiveMax = $maxBytes !== null ? min($maxBytes, $remaining) : $remaining;

        $data = $this->inner->tryRead($effectiveMax);
        $this->bytesRead += strlen($data);

        return $data;
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws ProtocolException If the inner handle has more data beyond the limit.
     * @throws CancelledException
     */
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        if ($this->bytesRead >= $this->limit) {
            return $this->handleLimitReached();
        }

        $remaining = $this->limit - $this->bytesRead;
        $effectiveMax = $maxBytes !== null ? min($maxBytes, $remaining) : $remaining;

        $data = $this->inner->read($effectiveMax, $cancellation);
        $this->bytesRead += strlen($data);

        return $data;
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has already been closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     */
    public function reachedEndOfDataSource(): bool
    {
        if ($this->limitReached) {
            return true;
        }

        return $this->inner->reachedEndOfDataSource();
    }

    /**
     * @throws ProtocolException If the inner handle still has data.
     * @throws Exception\AlreadyClosedException If the handle has already been closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     */
    private function handleLimitReached(): string
    {
        if ($this->limitReached || $this->inner->reachedEndOfDataSource()) {
            $this->limitReached = true;

            return '';
        }

        $peek = $this->inner->tryRead(1);
        if ($peek === '') {
            $this->limitReached = true;

            return '';
        }

        throw ProtocolException::forResponseBodyTooLarge($this->limit);
    }
}
