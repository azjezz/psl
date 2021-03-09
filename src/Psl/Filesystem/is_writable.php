<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_writable as php_is_writable;

/**
 * Check whether $filename is writable.
 *
 * @param string $filename Path to the file.
 *
 * If $filename is a relative filename, it will be checked relative to
 * the current working directory.
 *
 * @return bool true if the file or directory specified by $filename exists
 *              and is writable, false otherwise.
 */
function is_writable(string $filename): bool
{
    return php_is_writable($filename);
}
