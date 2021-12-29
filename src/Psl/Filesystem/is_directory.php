<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_dir;

/**
 * Check whether $node is a directory.
 *
 * If $node is a relative filename, it will be checked relative to
 * the current working directory.
 *
 * If $node is a symbolic or hard link then the link will be resolved and checked.
 *
 * @param non-empty-string $node
 *
 * @return bool true if $node exists and is a directory, false otherwise.
 */
function is_directory(string $node): bool
{
    return is_dir($node);
}
