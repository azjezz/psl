<?php

declare(strict_types=1);

namespace Psl\File;

use Psl;
use Psl\Filesystem;
use Psl\IO;

/**
 * Create a temporary file and open it for read and write.
 *
 * @throws Psl\Exception\InvariantViolationException If $directory doesn't exist or is not writable.
 * @throws Psl\Exception\InvariantViolationException If $prefix contains a directory separator.
 * @throws Filesystem\Exception\RuntimeException If unable to create the file.
 * @throws IO\Exception\BlockingException If unable to set the handle to non-blocking mode.
 */
function temporary(?string $directory = null, ?string $prefix = null): ReadWriteHandleInterface
{
    $path = Filesystem\create_temporary_file($directory, $prefix);

    return new ReadWriteHandle($path);
}
