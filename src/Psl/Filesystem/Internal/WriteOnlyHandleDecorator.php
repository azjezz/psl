<?php

declare(strict_types=1);

namespace Psl\Filesystem\Internal;

use Psl\Filesystem;

/**
 * A write handle decorator to restrict the type of another handle.
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class WriteOnlyHandleDecorator implements Filesystem\WriteFileHandleInterface
{
    private Filesystem\WriteFileHandleInterface $handle;

    public function __construct(Filesystem\WriteFileHandleInterface $handle)
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
