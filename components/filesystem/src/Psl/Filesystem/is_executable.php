<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_executable as php_is_executable;

/**
 * Check whether $node exists and is an executable file
 * or a directory with `execute` permission.
 *
 * @param non-empty-string $node Path, absolute or relative to the current working directory.
 */
function is_executable(string $node): bool
{
    return php_is_executable($node);
}
