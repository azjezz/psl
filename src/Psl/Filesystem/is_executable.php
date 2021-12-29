<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_executable as php_is_executable;

/**
 * Check whether $node is executable.
 *
 * If $node is a relative filename, it will be checked relative to
 * the current working directory.
 *
 * @param non-empty-string $node
 *
 * @return bool true if the file or directory specified by $node exists and is executable, false otherwise.
 */
function is_executable(string $node): bool
{
    return php_is_executable($node);
}
