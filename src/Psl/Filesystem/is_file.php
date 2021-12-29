<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_file as php_is_file;

/**
 * Check whether $node is a regular file.
 *
 * If $node is a relative filename, it will be checked relative to
 * the current working directory.
 *
 * If $node is a symbolic or hard link then the link will be resolved and checked.
 *
 * @param non-empty-string $node
 *
 * @return bool true if $node exists and is a regular file, false otherwise.
 */
function is_file(string $node): bool
{
    return php_is_file($node);
}
