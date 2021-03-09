<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_dir;

/**
 * Check whether $filename is a directory.
 *
 * @param string $filename Path to the file.
 *
 * If $filename is a relative filename, it will be checked relative to
 * the current working directory.
 *
 * If $filename is a symbolic or hard link then the link
 * will be resolved and checked.
 *
 * @return bool true if $filename exists and is a directory, false otherwise.
 */
function is_directory(string $filename): bool
{
    return is_dir($filename);
}
