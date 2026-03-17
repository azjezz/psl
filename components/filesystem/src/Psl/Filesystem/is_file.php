<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_file as php_is_file;

/**
 * Check whether $node exists and is a regular file or a link to one.
 *
 * @param non-empty-string $node Path, absolute or relative to the current working directory.
 *                               If it is a link, it will be resolved and checked.
 */
function is_file(string $node): bool
{
    return php_is_file($node);
}
