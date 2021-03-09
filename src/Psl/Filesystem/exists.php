<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function file_exists;

/**
 * Check whether $filename exists.
 *
 * @param string $filename Path to the file.
 *
 * If $filename is a relative filename, it will be checked relative to
 * the current working directory.
 *
 * @return bool true if $filename exists, false otherwise.
 */
function exists(string $filename): bool
{
    return file_exists($filename);
}
