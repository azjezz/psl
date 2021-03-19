<?php

declare(strict_types=1);

namespace Psl\IO;

/**
 * A handle that is explicitly closeable.
 */
interface CloseHandleInterface extends HandleInterface
{
    /**
     * Close the handle.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If unable to close the handle.
     */
    public function close(): void;
}
