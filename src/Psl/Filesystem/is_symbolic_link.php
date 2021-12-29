<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_link as php_is_link;

/**
 * Check whether $symbolic_link is a symbolic link.
 *
 * If $node is a relative filename, it will be checked relative to
 * the current working directory.
 *
 * @param non-empty-string $node
 *
 * @return bool true if the file or directory specified by $node exists and is a symbolic link, false otherwise.
 */
function is_symbolic_link(string $node): bool
{
    return php_is_link($node);
}
