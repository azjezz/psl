<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_executable as php_is_executable;

/**
 * Check whether $filename is executable.
 *
 * @param string $filename Path to the file.
 *
 * If $filename is a relative filename, it will be checked relative to
 * the current working directory.
 *
 * @return bool true if the file or directory specified by $filename exists
 *              and is executable, false otherwise.
 */
function is_executable(string $filename): bool
{
    return php_is_executable($filename);
}
