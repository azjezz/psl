<?php

declare(strict_types=1);

namespace Psl\IO\Tests\Unit;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

/**
 * A test handle that returns empty on the first N reads before yielding data.
 *
 * Simulates non-blocking stream behavior where read() returns '' before
 * data is available (e.g. TLS/TCP streams).
 */
final class DelayedHandle implements IO\ReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private int $emptyReadsRemaining;
    private IO\MemoryHandle $inner;

    /**
     * @param int<0, max> $emptyReads Number of empty reads before data is returned.
     */
    public function __construct(string $content, int $emptyReads = 1)
    {
        $this->inner = new IO\MemoryHandle($content);
        $this->emptyReadsRemaining = $emptyReads;
    }

    #[Override]
    public function tryRead(null|int $maxBytes = null): string
    {
        if ($this->emptyReadsRemaining > 0) {
            $this->emptyReadsRemaining--;
            return '';
        }

        return $this->inner->tryRead($maxBytes);
    }

    #[Override]
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        if ($this->emptyReadsRemaining > 0) {
            $this->emptyReadsRemaining--;
            return '';
        }

        return $this->inner->read($maxBytes, $cancellation);
    }

    #[Override]
    public function reachedEndOfDataSource(): bool
    {
        if ($this->emptyReadsRemaining > 0) {
            return false;
        }

        return $this->inner->reachedEndOfDataSource();
    }
}
