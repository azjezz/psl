<?php

declare(strict_types=1);

namespace Psl\Message\Internal;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

/**
 * Read handle that transparently concatenates two handles in sequence.
 *
 * Reads from the first handle until it reaches EOF, then reads from the second.
 *
 * @internal
 */
final class ConcatenatedReadHandle implements IO\ReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    /**
     * Whether the first handle has been fully consumed.
     */
    private bool $firstExhausted = false;

    /**
     * @param IO\ReadHandleInterface $first The handle to read from first.
     * @param IO\ReadHandleInterface $second The handle to read from after the first is exhausted.
     */
    public function __construct(
        private readonly IO\ReadHandleInterface $first,
        private readonly IO\ReadHandleInterface $second,
    ) {}

    /**
     * Attempt a non-blocking read from the current handle.
     *
     * Reads from the first handle until exhausted, then transparently
     * switches to the second.
     */
    public function tryRead(null|int $maxBytes = null): string
    {
        if (!$this->firstExhausted) {
            $chunk = $this->first->tryRead($maxBytes);
            if ($chunk !== '' || !$this->first->reachedEndOfDataSource()) {
                return $chunk;
            }

            $this->firstExhausted = true;
        }

        return $this->second->tryRead($maxBytes);
    }

    /**
     * Read from the current handle, blocking if necessary.
     *
     * Reads from the first handle until exhausted, then transparently
     * switches to the second.
     */
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        if (!$this->firstExhausted) {
            $chunk = $this->first->read($maxBytes, $cancellation);
            if ($chunk !== '' || !$this->first->reachedEndOfDataSource()) {
                return $chunk;
            }

            $this->firstExhausted = true;
        }

        return $this->second->read($maxBytes, $cancellation);
    }

    /**
     * Check whether both handles have been fully consumed.
     */
    public function reachedEndOfDataSource(): bool
    {
        return $this->firstExhausted && $this->second->reachedEndOfDataSource();
    }
}
