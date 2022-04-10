<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_writable as php_is_writable;

/**
 * Check whether $node exists and is writable.
 *
 * @param non-empty-string $node Path, absolute or relative to the current working directory.
 */
function is_writable(string $node): bool
{
    return php_is_writable($node);
}
