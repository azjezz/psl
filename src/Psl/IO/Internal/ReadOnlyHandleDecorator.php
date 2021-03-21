<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl\IO\ReadHandleInterface;

/**
 * A read handle decorator to restrict the type of another handle.
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class ReadOnlyHandleDecorator implements ReadHandleInterface
{
    private ReadHandleInterface $handle;

    public function __construct(ReadHandleInterface $handle)
    {
        $this->handle = $handle;
    }

    public function readImmediately(?int $max_bytes = null): string
    {
        return $this->handle->readImmediately($max_bytes);
    }

    public function read(?int $max_bytes = null, ?int $timeout_ms = null): string
    {
        return $this->handle->read($max_bytes, $timeout_ms);
    }

    public function readAll(?int $max_bytes = null, ?int $timeout_ms = null): string
    {
        return $this->handle->readAll($max_bytes, $timeout_ms);
    }

    public function readFixedSize(int $size, ?int $timeout_ms = null): string
    {
        return $this->handle->readFixedSize($size, $timeout_ms);
    }
}
