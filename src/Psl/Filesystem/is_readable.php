<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_readable as php_is_readable;

/**
 * Check whether $filename is readable.
 *
 * @param string $filename Path to the file.
 *
 * If $filename is a relative filename, it will be checked relative to
 * the current working directory.
 *
 * @return bool true if the file or directory specified by $filename exists
 *              and is readable, false otherwise.
 */
function is_readable(string $filename): bool
{
    return php_is_readable($filename);
}
