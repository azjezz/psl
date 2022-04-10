<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_link as php_is_link;

/**
 * Check whether $symbolic_link exists and is a symbolic link.
 *
 * @param non-empty-string $node Path, absolute or relative to the current working directory.
 */
function is_symbolic_link(string $node): bool
{
    return php_is_link($node);
}
