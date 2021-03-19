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
