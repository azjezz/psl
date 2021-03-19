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

    /**
     * {@inheritDoc}
     */
    public function read(?int $max_bytes = null): string
    {
        return $this->handle->read($max_bytes);
    }
}
