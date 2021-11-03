<?php

declare(strict_types=1);

namespace Psl\File;

use Psl;
use Psl\IO;

/**
 * Open a file handle for read only.
 *
 * @throws IO\Exception\BlockingException If unable to set the stream to non-blocking mode.
 * @throws Psl\Exception\InvariantViolationException If $path does not point to a file, or is not readable. *
 */
function open_read_only(string $path): ReadHandleInterface
{
    return new ReadHandle($path);
}
