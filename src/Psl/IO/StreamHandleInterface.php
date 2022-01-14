<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\IO;

interface StreamHandleInterface extends IO\HandleInterface
{
    /**
     * Return the underlying stream resource or object.
     *
     * If the stream has been closed, this method will return null.
     *
     * @return resource|null
     */
    public function getStream(): mixed;
}
