<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_readable as php_is_readable;

/**
 * Check whether $node is readable.
 *
 * If $node is a relative filename, it will be checked relative to
 * the current working directory.
 *
 * @param non-empty-string $node
 *
 * @return bool true if the file or directory specified by $node exists and is readable, false otherwise.
 */
function is_readable(string $node): bool
{
    return php_is_readable($node);
}
