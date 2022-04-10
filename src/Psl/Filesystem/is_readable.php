<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_readable as php_is_readable;

/**
 * Check whether $node exists and is readable.
 *
 * @param non-empty-string $node Path, absolute or relative to the current working directory.
 */
function is_readable(string $node): bool
{
    return php_is_readable($node);
}
