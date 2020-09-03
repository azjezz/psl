<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl\IO\ReadHandle;

/**
 * A read handle decorator to restrict the type of another handle.
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class ReadOnlyHandleDecorator implements ReadHandle
{
    private ReadHandle $handle;

    public function __construct(ReadHandle $handle)
    {
        $this->handle = $handle;
    }

    /**
     * {@inheritDoc}
     */
    public function read(?int $max_bytes = null): string
    {
        return $this->handle->read($max_bytes);
    }
}
