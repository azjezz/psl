<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl\IO\WriteHandle;

/**
 * A write handle decorator to restrict the type of another handle.
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class WriteOnlyHandleDecorator implements WriteHandle
{
    private WriteHandle $handle;

    public function __construct(WriteHandle $handle)
    {
        $this->handle = $handle;
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $bytes): int
    {
        return $this->handle->write($bytes);
    }

    /**
     * {@inheritDoc}
     */
    public function flush(): void
    {
        $this->handle->flush();
    }
}
