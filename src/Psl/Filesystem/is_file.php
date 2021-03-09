<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_file as php_is_file;

/**
 * Check whether $filename is a regular file.
 *
 * @param string $filename Path to the file.
 *
 * If $filename is a relative filename, it will be checked relative to
 * the current working directory.
 *
 * If $filename is a symbolic or hard link then the link
 * will be resolved and checked.
 *
 * @return bool true if $filename exists and is a regular file, false otherwise.
 */
function is_file(string $filename): bool
{
    return php_is_file($filename);
}
