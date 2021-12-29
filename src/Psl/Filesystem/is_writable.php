<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_writable as php_is_writable;

/**
 * Check whether $node is writable.
 *
 * If $node is a relative filename, it will be checked relative to
 * the current working directory.
 *
 * @param non-empty-string $node
 *
 * @return bool true if the file or directory specified by $node exists and is writable, false otherwise.
 */
function is_writable(string $node): bool
{
    return php_is_writable($node);
}
