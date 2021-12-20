<?php

declare(strict_types=1);

namespace Psl\File;

use Psl;

/**
 * Open a file handle for read only.
 *
 * @param non-empty-string $path
 *
 * @throws Psl\Exception\InvariantViolationException If $path does not point to a file, or is not readable. *
 */
function open_read_only(string $path): ReadHandleInterface
{
    return new ReadHandle($path);
}
