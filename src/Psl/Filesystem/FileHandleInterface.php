<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\IO;

interface FileHandleInterface extends IO\CloseSeekHandleInterface
{
    /**
     * Gets the path to the file.
     */
    public function getPath(): string;
}
