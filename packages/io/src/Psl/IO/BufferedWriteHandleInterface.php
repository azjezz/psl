<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;

/**
 * A buffered writable handle that provides explicit flushing
 * on top of {@see WriteHandleInterface}.
 */
interface BufferedWriteHandleInterface extends WriteHandleInterface
{
    /**
     * Flush any buffered output to the underlying handle.
     *
     * @throws Exception\AlreadyClosedException If the handle has already been closed.
     * @throws Exception\RuntimeException If the flush operation fails.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    public function flush(CancellationTokenInterface $cancellation = new NullCancellationToken()): void;
}
