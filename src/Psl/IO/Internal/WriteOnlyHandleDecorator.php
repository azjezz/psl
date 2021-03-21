<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl\IO\WriteHandleInterface;

/**
 * A write handle decorator to restrict the type of another handle.
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class WriteOnlyHandleDecorator implements WriteHandleInterface
{
    private WriteHandleInterface $handle;

    public function __construct(WriteHandleInterface $handle)
    {
        $this->handle = $handle;
    }

    public function writeImmediately(string $bytes): int
    {
        return $this->handle->writeImmediately($bytes);
    }

    public function write(string $bytes, ?int $timeout_ms = null): int
    {
        return $this->handle->write($bytes, $timeout_ms);
    }

    public function writeAll(string $bytes, ?int $timeout_ms = null): void
    {
        $this->handle->writeAll($bytes, $timeout_ms);
    }
}
