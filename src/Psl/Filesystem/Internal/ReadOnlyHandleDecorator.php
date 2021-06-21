<?php

declare(strict_types=1);

namespace Psl\Filesystem\Internal;

use Psl\Filesystem;

/**
 * A read handle decorator to restrict the type of another handle.
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class ReadOnlyHandleDecorator implements Filesystem\ReadFileHandleInterface
{
    private Filesystem\ReadFileHandleInterface $handle;

    public function __construct(Filesystem\ReadFileHandleInterface $handle)
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

    public function close(): void
    {
        $this->handle->close();
    }

    public function seek(int $offset): void
    {
        $this->handle->seek($offset);
    }

    public function tell(): int
    {
        return $this->handle->tell();
    }

    public function getPath(): string
    {
        return $this->handle->getPath();
    }
}
