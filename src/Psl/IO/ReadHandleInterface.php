<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Exception\InvariantViolationException;

/**
 * An `IO\Handle` that is readable.
 */
interface ReadHandleInterface extends HandleInterface
{
    /**
     * An immediate, unordered read.
     *
     * @param int|null $max_bytes the maximum number of bytes to read.
     *
     * up to `$max_bytes` may be allocated in a buffer; large values may lead
     * to unnecessarily hitting the request memory limit.
     *
     * @returns string the read data on success, or an empty string if the end of file is reached.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws InvariantViolationException If $max_bytes is 0.
     */
    public function read(?int $max_bytes = null): string;
}
