<?php

declare(strict_types=1);

namespace Psl\File;

use Psl;
use Psl\Filesystem;

/**
 * Open a file handle for write only.
 *
 * @throws Psl\Exception\InvariantViolationException If $filename points to a non-file node, or it not writeable.
 * @throws Filesystem\Exception\RuntimeException If unable to create $path when it does not exist.
 */
function open_write_only(string $filename, WriteMode $mode = WriteMode::OPEN_OR_CREATE): WriteHandleInterface
{
    return new WriteHandle($filename, $mode);
}
