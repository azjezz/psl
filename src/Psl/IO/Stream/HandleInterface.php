<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;

interface HandleInterface extends IO\HandleInterface
{
    /**
     * Return the underlying stream resource or object.
     *
     * If the stream has been closed, this method will return null.
     *
     * @return resource|object|null
     */
    public function getStream(): mixed;
}
