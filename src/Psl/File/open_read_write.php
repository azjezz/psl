<?php

declare(strict_types=1);

namespace Psl\File;

use Psl;
use Psl\Filesystem;

/**
 * Open a file handle for read and write.
 *
 * @param non-empty-string $path
 *
 * @throws Psl\Exception\InvariantViolationException If $path points to a non-file node, or it not writeable.
 * @throws Filesystem\Exception\RuntimeException If unable to create $path when it does not exist.
 */
function open_read_write(string $path, WriteMode $write_mode = WriteMode::OPEN_OR_CREATE): ReadWriteHandleInterface
{
    return new ReadWriteHandle($path, $write_mode);
}
